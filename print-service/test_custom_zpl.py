"""Focused validation tests for Professional custom ZPL templates.

Run with the Print Service virtualenv: python -m unittest test_custom_zpl
"""

from __future__ import annotations

import os
import tempfile
import unittest
from unittest.mock import patch

TEST_STATE = tempfile.TemporaryDirectory(prefix="zebrashipping-zpl-test-")
os.environ["ZEBRASHIPPING_STATE_DIR"] = TEST_STATE.name
os.environ["ZEBRASHIPPING_CONFIG"] = os.path.join(TEST_STATE.name, "printers.json")

import app  # noqa: E402


PRINTER = {"dpi": 203, "width_mm": 93, "height_mm": 71}
LABEL = {
    "recipient": "Astra ^XA~",
    "address": "Detelina 70",
    "city": "Plovdiv",
}


class CustomZplTests(unittest.TestCase):
    def test_template_schema_accepts_supported_placeholders(self) -> None:
        request = app.PrintRequest(
            request_id="test-request-001",
            printer_id="printer-test",
            installation_id="installation-test",
            label=LABEL,
            zpl_template="^XA^PW{{width_dots}}^FD{{recipient}}^FS^XZ",
        )
        self.assertIsNotNone(request.zpl_template)

    def test_template_schema_rejects_unknown_placeholders_and_bad_framing(self) -> None:
        for template in ("^XA^FD{{unknown}}^FS^XZ", "^FD{{recipient}}^FS"):
            with self.subTest(template=template), self.assertRaises(ValueError):
                app.PrintRequest(
                    request_id="test-request-002",
                    printer_id="printer-test",
                    installation_id="installation-test",
                    label=LABEL,
                    zpl_template=template,
                )

    def test_custom_renderer_substitutes_dimensions_and_cleans_values(self) -> None:
        rendered = app._custom_zpl(
            "^XA^PW{{width_dots}}^LL{{height_dots}}^FD{{recipient}}^FS^XZ",
            LABEL,
            PRINTER,
        ).decode("utf-8")
        self.assertIn("^PW743", rendered)
        self.assertIn("^LL567", rendered)
        self.assertIn("^FDAstra  XA^FS", rendered)
        self.assertNotIn("^XA~", rendered)

    def test_demo_cannot_submit_a_custom_template(self) -> None:
        request = app.PrintRequest(
            request_id="test-request-003",
            printer_id="printer-test",
            installation_id="installation-test",
            label=LABEL,
            zpl_template="^XA^FD{{recipient}}^FS^XZ",
        )
        with patch.object(app, "_authorized"), patch.object(
            app, "_license_status", return_value={"edition": "demo", "valid": True}
        ), self.assertRaises(app.HTTPException) as error:
            app.print_label(request, None)
        self.assertEqual(error.exception.status_code, 403)
        self.assertIn("Professional", error.exception.detail)

    def test_default_demo_layout_keeps_demo_mark(self) -> None:
        rendered = app._zpl(LABEL, PRINTER, demo=True).decode("utf-8")
        self.assertIn("^FDDEMO^FS", rendered)

    def test_default_layout_prints_notes(self) -> None:
        label = {**LABEL, "notes": "Handle with care"}
        rendered = app._zpl(label, PRINTER, demo=False).decode("utf-8")
        self.assertIn("^FDБележки: Handle with care^FS", rendered)

    def test_default_layout_omits_empty_notes(self) -> None:
        label = {**LABEL, "notes": ""}
        rendered = app._zpl(label, PRINTER, demo=False).decode("utf-8")
        self.assertNotIn("Бележки:", rendered)

    def test_copy_count_is_bounded_and_repeats_complete_label(self) -> None:
        request = app.PrintRequest(
            request_id="test-request-004",
            printer_id="printer-test",
            installation_id="installation-test",
            label=LABEL,
            copies=3,
        )
        self.assertEqual(request.copies, 3)
        self.assertEqual(app._repeat_label(b"^XA^XZ", 3), b"^XA^XZ^XA^XZ^XA^XZ")
        for copies in (0, 101):
            with self.subTest(copies=copies), self.assertRaises(ValueError):
                app._repeat_label(b"^XA^XZ", copies)
        with self.assertRaises(ValueError):
            app.PrintRequest(
                request_id="test-request-005",
                printer_id="printer-test",
                installation_id="installation-test",
                label=LABEL,
                copies=101,
            )


if __name__ == "__main__":
    unittest.main()
