"""Loopback-only Zebra print service. Printer addresses are supplied by admins."""

from __future__ import annotations

import base64
import hmac
import ipaddress
import json
import os
import re
import socket
import sqlite3
import tempfile
import textwrap
import uuid
from datetime import datetime, timedelta, timezone
from pathlib import Path
from typing import Any

from cryptography.exceptions import InvalidSignature
from cryptography.hazmat.primitives.asymmetric.ed25519 import Ed25519PublicKey
from fastapi import FastAPI, Header, HTTPException
from pydantic import BaseModel, Field, field_validator

APP_VERSION = "0.2.3"
DEMO_DAYS = 14
DEMO_PRINT_LIMIT = 50
DEMO_PRINTER_LIMIT = 2
TOKEN = os.environ.get("ZEBRASHIPPING_API_TOKEN", "")
CONFIG_PATH = Path(os.environ.get("ZEBRASHIPPING_CONFIG", "./config.json"))
STATE_DIR = Path(os.environ.get("ZEBRASHIPPING_STATE_DIR", "./state"))
DATABASE_PATH = STATE_DIR / "service.sqlite3"
PUBLIC_KEY_B64 = os.environ.get("ZEBRASHIPPING_LICENSE_PUBLIC_KEY", "")
STATE_DIR.mkdir(parents=True, exist_ok=True)

app = FastAPI(title="GLPI Zebra Shipping Print Service", version=APP_VERSION)


def _utcnow() -> datetime:
    return datetime.now(timezone.utc)


def _connect() -> sqlite3.Connection:
    db = sqlite3.connect(DATABASE_PATH, timeout=10)
    db.row_factory = sqlite3.Row
    db.execute("PRAGMA journal_mode=WAL")
    return db


def _init_db() -> None:
    with _connect() as db:
        db.executescript("""
            CREATE TABLE IF NOT EXISTS trials (
                installation_id TEXT PRIMARY KEY,
                started_at TEXT NOT NULL,
                last_seen_at TEXT NOT NULL,
                print_count INTEGER NOT NULL DEFAULT 0
            );
            CREATE TABLE IF NOT EXISTS jobs (
                id TEXT PRIMARY KEY,
                request_id TEXT NOT NULL UNIQUE,
                installation_id TEXT NOT NULL,
                printer_id TEXT NOT NULL,
                status TEXT NOT NULL,
                created_at TEXT NOT NULL,
                error_code TEXT
            );
        """)
    try:
        os.chmod(DATABASE_PATH, 0o600)
    except OSError:
        pass


_init_db()


class Printer(BaseModel):
    id: str = Field(min_length=1, max_length=64, pattern=r"^[a-zA-Z0-9_-]+$")
    name: str = Field(min_length=1, max_length=100)
    host: str = Field(min_length=1, max_length=253)
    port: int = Field(default=9100, ge=1, le=65535)
    dpi: int = Field(default=203, ge=152, le=600)
    width_mm: float = Field(ge=20, le=300)
    height_mm: float = Field(ge=20, le=300)
    enabled: bool = True
    description: str = Field(default="", max_length=250)

    @field_validator("host")
    @classmethod
    def host_is_plain_name_or_ip(cls, value: str) -> str:
        value = value.strip()
        if any(ch in value for ch in "/\\@\x00") or value.startswith("."):
            raise ValueError("Enter a printer IP address or hostname")
        return value


class LicenseStatusRequest(BaseModel):
    installation_id: str = Field(min_length=8, max_length=80)
    trial_started_at: str | None = Field(default=None, max_length=40)
    license_file: str | None = Field(default=None, max_length=8192)


class PrinterConfigurationRequest(LicenseStatusRequest):
    printers: list[Printer]


class PrintRequest(BaseModel):
    request_id: str = Field(min_length=8, max_length=64, pattern=r"^[a-zA-Z0-9_-]+$")
    printer_id: str = Field(min_length=1, max_length=64)
    installation_id: str = Field(min_length=8, max_length=80)
    trial_started_at: str | None = Field(default=None, max_length=40)
    license_file: str | None = Field(default=None, max_length=8192)
    label: dict[str, str] = Field()

    @field_validator("label")
    @classmethod
    def bounded_label_values(cls, values: dict[str, str]) -> dict[str, str]:
        allowed = {"city", "recipient", "address", "phone", "asset_name", "asset_type",
                   "inventory_number", "serial_number", "notes"}
        if set(values) - allowed or len(values) > len(allowed):
            raise ValueError("Unsupported label field")
        if any(len(value) > 500 for value in values.values()):
            raise ValueError("Label fields must be at most 500 characters")
        return values


def _authorized(authorization: str | None) -> None:
    if len(TOKEN) < 32 or TOKEN in {"replace-with-a-random-secret", "change-me"}:
        raise HTTPException(503, "Print service authentication is not configured")
    if not hmac.compare_digest(authorization or "", f"Bearer {TOKEN}"):
        raise HTTPException(401, "Unauthorized")


def _read_config() -> dict[str, Any]:
    try:
        data = json.loads(CONFIG_PATH.read_text(encoding="utf-8"))
        if not isinstance(data.get("printers"), list):
            raise ValueError("printers must be a list")
        return data
    except (OSError, json.JSONDecodeError, ValueError) as exc:
        raise HTTPException(503, "Printer configuration is unavailable") from exc


def _save_config(data: dict[str, Any]) -> None:
    CONFIG_PATH.parent.mkdir(parents=True, exist_ok=True)
    fd, temp_name = tempfile.mkstemp(prefix="zebrashipping-", dir=CONFIG_PATH.parent)
    try:
        with os.fdopen(fd, "w", encoding="utf-8") as stream:
            json.dump(data, stream, ensure_ascii=False, indent=2)
            stream.flush()
            os.fsync(stream.fileno())
        os.chmod(temp_name, 0o600)
        os.replace(temp_name, CONFIG_PATH)
    finally:
        if os.path.exists(temp_name):
            os.unlink(temp_name)


def _resolve_private(host: str) -> list[str]:
    try:
        candidates = [ipaddress.ip_address(host)]
    except ValueError:
        try:
            resolved = socket.getaddrinfo(host, None, type=socket.SOCK_STREAM)
        except OSError as exc:
            raise HTTPException(422, "Printer hostname could not be resolved") from exc
        candidates = [ipaddress.ip_address(row[4][0].split("%", 1)[0]) for row in resolved]
    unique = list({str(addr): addr for addr in candidates}.values())
    if not unique or any(not addr.is_private or addr.is_loopback or addr.is_multicast or addr.is_unspecified for addr in unique):
        raise HTTPException(422, "Printers must resolve to private network addresses")
    return [str(addr) for addr in unique]


def _find_printer(printer_id: str) -> dict[str, Any]:
    for printer in _read_config()["printers"]:
        if printer.get("id") == printer_id and printer.get("enabled") is True:
            return printer
    raise HTTPException(404, "Printer not found or disabled")


def _professional_license(encoded: str | None, installation_id: str) -> dict[str, Any] | None:
    if not encoded:
        return None
    if not PUBLIC_KEY_B64:
        raise HTTPException(503, "Professional license verification is not configured")
    try:
        packed = base64.urlsafe_b64decode(encoded + "=" * (-len(encoded) % 4))
        envelope = json.loads(packed)
        payload = envelope["payload"]
        signature = base64.b64decode(envelope["signature"], validate=True)
        key = Ed25519PublicKey.from_public_bytes(base64.b64decode(PUBLIC_KEY_B64, validate=True))
        signed = json.dumps(payload, sort_keys=True, separators=(",", ":")).encode()
        key.verify(signature, signed)
    except (ValueError, KeyError, TypeError, json.JSONDecodeError, InvalidSignature) as exc:
        raise HTTPException(403, "Invalid Professional license") from exc
    if (payload.get("product") != "glpi-zebra-shipping"
            or payload.get("edition") != "professional"
            or payload.get("major_version") != 1
            or payload.get("installation_id") != installation_id):
        raise HTTPException(403, "License does not match this GLPI installation")
    return payload


def _license_status(installation_id: str, encoded: str | None = None,
                    trial_started_at: str | None = None) -> dict[str, Any]:
    professional = _professional_license(encoded, installation_id)
    if professional:
        return {"edition": "professional", "valid": True, "expires_at": None}
    now = _utcnow()
    with _connect() as db:
        row = db.execute("SELECT * FROM trials WHERE installation_id=?", (installation_id,)).fetchone()
        if row is None:
            started_at = now
            if trial_started_at:
                try:
                    requested_start = datetime.fromisoformat(trial_started_at.replace("Z", "+00:00"))
                    if requested_start.tzinfo is None:
                        requested_start = requested_start.replace(tzinfo=timezone.utc)
                    if requested_start <= now:
                        started_at = requested_start.astimezone(timezone.utc)
                except ValueError:
                    raise HTTPException(422, "Invalid trial start date")
            db.execute("INSERT INTO trials(installation_id, started_at, last_seen_at) VALUES(?,?,?)",
                       (installation_id, started_at.isoformat(), now.isoformat()))
            print_count = 0
        else:
            started_at = datetime.fromisoformat(row["started_at"])
            print_count = int(row["print_count"])
            db.execute("UPDATE trials SET last_seen_at=? WHERE installation_id=?",
                       (now.isoformat(), installation_id))
    expires = started_at + timedelta(days=DEMO_DAYS)
    valid = now < expires and print_count < DEMO_PRINT_LIMIT
    return {"edition": "demo", "valid": valid, "started_at": started_at.isoformat(),
            "expires_at": expires.isoformat(), "prints_remaining": max(0, DEMO_PRINT_LIMIT - print_count),
            "printer_limit": DEMO_PRINTER_LIMIT}


def _clean_zpl_text(value: str) -> str:
    # Escape ZPL command markers and ASCII control characters from user-provided data.
    return re.sub(r"[\^~\r\n\x00-\x1f]", " ", value).strip()[:180]


def _wrap_label_text(value: str, width: int, font_size: int, max_lines: int) -> list[str]:
    # The selected Zebra font is proportional; use a conservative character
    # width estimate so long Cyrillic names and addresses wrap before the edge.
    chars_per_line = max(1, int(width / max(1, font_size * 0.56)))
    lines = textwrap.wrap(value, width=chars_per_line, break_long_words=True,
                          break_on_hyphens=False) if value else []
    if len(lines) > max_lines:
        raise HTTPException(422, "Label content does not fit; shorten recipient, address, or asset metadata")
    return lines


def _zpl(label: dict[str, str], printer: dict[str, Any], demo: bool) -> bytes:
    dpi = float(printer["dpi"])
    scale = dpi / 203.0
    margin = max(4, round(20 * scale))
    width = round(float(printer["width_mm"]) * dpi / 25.4)
    height = round(float(printer["height_mm"]) * dpi / 25.4)
    content_width = width - margin * 2
    y = margin
    commands = ["^XA", f"^PW{width}", f"^LL{height}", "^CI28", "^CWX,E:TT0003M_.FNT^FS"]
    if demo:
        watermark_size = max(12, round(20 * scale))
        commands.append(f"^FO{max(4, width - round(110 * scale))},{round(12 * scale)}^AXN,{watermark_size},{watermark_size}^FDDEMO^FS")

    header_size = max(16, round(28 * scale))
    commands.append(
        f"^FO{margin},{y}^AXN,{header_size},{header_size}^FB{content_width},1,0,L,0^FDИЗПРАЩАНЕ НА ТЕХНИКА^FS"
    )
    y += header_size + round(8 * scale)

    contact = _clean_zpl_text(label.get("recipient", ""))
    city = _clean_zpl_text(label.get("city", ""))
    recipient = (contact or city).upper()
    recipient_size = max(32, round(76 * scale))
    recipient_lines = _wrap_label_text(recipient, content_width, recipient_size, max_lines=2)
    for line in recipient_lines:
        commands.append(
            f"^FO{margin},{y}^AXN,{recipient_size},{recipient_size}^FB{content_width},1,0,C,0^FD{line}^FS"
        )
        y += recipient_size + round(4 * scale)

    y += round(5 * scale)
    commands.append(f"^FO{margin},{y}^GB{content_width},2,2^FS")
    y += round(14 * scale)

    address = _clean_zpl_text(label.get("address", ""))
    if contact and city and city.casefold() not in address.casefold():
        address = f"{address}, {city}" if address else city
    address_text = f"Адрес: {address}" if address else ""
    address_size = max(24, round(48 * scale))
    for line in _wrap_label_text(address_text, content_width, address_size, max_lines=3):
        commands.append(
            f"^FO{margin},{y}^AXN,{address_size},{address_size}^FB{content_width},1,0,L,0^FD{line}^FS"
        )
        y += address_size + round(4 * scale)

    y += round(5 * scale)
    commands.append(f"^FO{margin},{y}^GB{content_width},2,2^FS")
    y += round(12 * scale)

    metadata_fields = [
        ("Актив", _clean_zpl_text(label.get("asset_name", "")) or "N/A"),
        ("Тип", _clean_zpl_text(label.get("asset_type", "")) or "N/A"),
        ("Инв. №", _clean_zpl_text(label.get("inventory_number", "")) or "N/A"),
        ("Сериен №", _clean_zpl_text(label.get("serial_number", "")) or "N/A"),
    ]
    phone = _clean_zpl_text(label.get("phone", ""))
    phone_size = max(16, round(26 * scale))
    phone_lines = _wrap_label_text(f"Телефон: {phone}", content_width, phone_size, max_lines=2) if phone else []

    metadata_size = max(18, round(30 * scale))
    minimum_metadata_size = max(14, round(18 * scale))
    metadata_gap = max(2, round(3 * scale))
    metadata_lines: list[str] = []
    while metadata_size >= minimum_metadata_size:
        candidate_lines = []
        try:
            for caption, value in metadata_fields:
                candidate_lines.extend(
                    _wrap_label_text(f"{caption}: {value}", content_width, metadata_size, max_lines=2)
                )
        except HTTPException:
            candidate_lines = []
        candidate_height = len(candidate_lines) * (metadata_size + metadata_gap)
        phone_height = len(phone_lines) * (phone_size + round(3 * scale))
        if candidate_lines and y + candidate_height + phone_height <= height - margin:
            metadata_lines = candidate_lines
            break
        metadata_size -= 2

    if not metadata_lines:
        raise HTTPException(422, "Label content does not fit; shorten recipient, address, or asset metadata")

    for line in metadata_lines:
        commands.append(
            f"^FO{margin},{y}^AXN,{metadata_size},{metadata_size}^FB{content_width},1,0,L,0^FD{line}^FS"
        )
        y += metadata_size + metadata_gap

    if phone:
        for line in phone_lines:
            commands.append(
                f"^FO{margin},{y}^AXN,{phone_size},{phone_size}^FB{content_width},1,0,L,0^FD{line}^FS"
            )
            y += phone_size + round(3 * scale)

    if y > height - margin:
        raise HTTPException(422, "Label content does not fit; shorten recipient, address, or asset metadata")
    commands.append("^XZ")
    return "\n".join(commands).encode("utf-8")


def _job(row: sqlite3.Row) -> dict[str, Any]:
    return {"id": row["id"], "request_id": row["request_id"], "printer_id": row["printer_id"],
            "status": row["status"], "created_at": row["created_at"], "error": row["error_code"]}


@app.get("/health")
def health() -> dict[str, str]:
    return {"status": "ok", "version": APP_VERSION}


@app.post("/v1/license/status")
def license_status(request: LicenseStatusRequest,
                   authorization: str | None = Header(default=None)) -> dict[str, Any]:
    _authorized(authorization)
    if not re.fullmatch(r"[a-zA-Z0-9_-]{8,80}", request.installation_id):
        raise HTTPException(422, "Invalid installation ID")
    return _license_status(request.installation_id, request.license_file, request.trial_started_at)


@app.get("/v1/printers")
def printers(authorization: str | None = Header(default=None)) -> dict[str, Any]:
    _authorized(authorization)
    return {"printers": _read_config()["printers"]}


@app.put("/v1/printers")
def configure_printers(request: PrinterConfigurationRequest,
                       authorization: str | None = Header(default=None)) -> dict[str, Any]:
    _authorized(authorization)
    license_state = _license_status(request.installation_id, request.license_file, request.trial_started_at)
    ids = [p.id for p in request.printers]
    if len(ids) != len(set(ids)):
        raise HTTPException(422, "Printer IDs must be unique")
    if license_state["edition"] == "demo" and len([p for p in request.printers if p.enabled]) > DEMO_PRINTER_LIMIT:
        raise HTTPException(403, f"Demo edition allows up to {DEMO_PRINTER_LIMIT} enabled printers")
    for printer in request.printers:
        _resolve_private(printer.host)
    _save_config({"printers": [p.model_dump() for p in request.printers]})
    return {"printers": [{k: v for k, v in p.model_dump().items() if k != "host"} for p in request.printers]}


@app.post("/v1/printers/{printer_id}/check")
def check_printer(printer_id: str, authorization: str | None = Header(default=None)) -> dict[str, Any]:
    _authorized(authorization)
    printer = _find_printer(printer_id)
    reachable = False
    for address in _resolve_private(printer["host"]):
        try:
            with socket.create_connection((address, int(printer["port"])), timeout=3):
                reachable = True
                break
        except OSError:
            continue
    return {"printer_id": printer_id, "reachable": reachable}


@app.post("/v1/print")
def print_label(request: PrintRequest, authorization: str | None = Header(default=None)) -> dict[str, Any]:
    _authorized(authorization)
    license_state = _license_status(request.installation_id, request.license_file, request.trial_started_at)
    if not license_state["valid"]:
        raise HTTPException(403, "License or demo period has expired")
    printer = _find_printer(request.printer_id)
    # Validate and render before reserving a demo print. Invalid content must
    # not consume a successful-job allowance or leave a queued history row.
    raw = _zpl(request.label, printer, license_state["edition"] == "demo")
    job_id = str(uuid.uuid4())
    created_at = _utcnow().isoformat()
    with _connect() as db:
        db.execute("BEGIN IMMEDIATE")
        existing = db.execute("SELECT * FROM jobs WHERE request_id=?", (request.request_id,)).fetchone()
        if existing:
            return _job(existing)
        if license_state["edition"] == "demo":
            trial = db.execute("SELECT print_count, started_at FROM trials WHERE installation_id=?",
                               (request.installation_id,)).fetchone()
            if trial is None or int(trial["print_count"]) >= DEMO_PRINT_LIMIT:
                raise HTTPException(403, "Demo print limit reached")
            expiry = datetime.fromisoformat(trial["started_at"]) + timedelta(days=DEMO_DAYS)
            if _utcnow() >= expiry:
                raise HTTPException(403, "Demo period has expired")
            db.execute("UPDATE trials SET print_count=print_count+1 WHERE installation_id=?",
                       (request.installation_id,))
        db.execute("INSERT INTO jobs(id,request_id,installation_id,printer_id,status,created_at,error_code) VALUES(?,?,?,?,?,?,NULL)",
                   (job_id, request.request_id, request.installation_id, request.printer_id, "queued", created_at))
    status, error_code = "failed", None
    try:
        for address in _resolve_private(printer["host"]):
            try:
                with socket.create_connection((address, int(printer["port"])), timeout=5) as conn:
                    conn.sendall(raw)
                status = "sent"
                break
            except OSError:
                continue
        if status != "sent":
            error_code = "printer_unreachable"
    except HTTPException:
        error_code = "printer_unreachable"
    except (OSError, KeyError, TypeError, ValueError):
        error_code = "print_failed"
    with _connect() as db:
        db.execute("UPDATE jobs SET status=?,error_code=? WHERE id=?", (status, error_code, job_id))
        if status == "failed" and license_state["edition"] == "demo":
            db.execute("UPDATE trials SET print_count=MAX(0,print_count-1),last_seen_at=? WHERE installation_id=?",
                       (created_at, request.installation_id))
        row = db.execute("SELECT * FROM jobs WHERE id=?", (job_id,)).fetchone()
    if status != "sent":
        raise HTTPException(502, "Print job failed; check printer connectivity")
    return _job(row)


@app.get("/v1/jobs/{request_id}")
def get_job(request_id: str, authorization: str | None = Header(default=None)) -> dict[str, Any]:
    _authorized(authorization)
    with _connect() as db:
        row = db.execute("SELECT * FROM jobs WHERE request_id=?", (request_id,)).fetchone()
    if row is None:
        raise HTTPException(404, "Print job not found")
    return _job(row)
