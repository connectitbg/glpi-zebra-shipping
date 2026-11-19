# Printer compatibility

GLPI Zebra Shipping is a stable, working network-printing plugin. Its printer transport sends ZPL over raw TCP to an administrator-configured printer address.

## Verified hardware

- **Zebra ZD421:** physical label printing has been demonstrated successfully over the network.
- **Media used for the accepted label layout:** 93 × 71 mm.
- **Print path:** GLPI plugin → local Print Service → printer raw TCP port (normally 9100).

The ZD421 is the only model that has been individually verified. The plugin is designed for Zebra printers and is expected to work with other network printers that accept ZPL over raw TCP. That expectation is not a certification or a guarantee for every model, firmware, resolution, font, or media size; check the exact hardware and label stock before operational use.

## USB printers

USB printer compatibility is unknown and USB printing is not supported by the current product. The implementation is intentionally network-based. USB support is outside the project scope and is not planned because development effort is focused on networked printers.

## Configure a printer

Enter the printer's IP address or hostname, TCP port, DPI, and label dimensions in **Setup → Plugins → GLPI Zebra Shipping**. The package has no preconfigured printer address. Use **Test connection** before printing a label.
