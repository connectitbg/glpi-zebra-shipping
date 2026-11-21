# Custom ZPL label templates

Custom ZPL templates are a **Professional** feature. A valid Professional license is required both to save a template in GLPI and to use it through the Print Service. Demo installations show the editor as a locked preview and continue using the built-in label; the `DEMO` mark is retained on demo labels.

## Where to edit the template

Open **Setup → Plugins → GLPI Zebra Shipping** and find **Custom ZPL label template**. Paste a complete ZPL format, then select **Save ZPL template**. The value is stored with the GLPI plugin configuration and is included in the plugin's recovery export. Keep recovery exports private because they include the activation license.

To return to the built-in label layout, clear the editor and save it while a valid Professional license is active. Clearing the template does not remove printer settings or print history.

## Supported placeholders

Write each placeholder exactly as shown, including the double braces and lowercase spelling. The service replaces placeholders when a print job is rendered.

| Placeholder | Value inserted | Where the value comes from |
| --- | --- | --- |
| `{{width_dots}}` | Configured media width in printer dots | Printer width in millimeters × DPI ÷ 25.4, rounded to the nearest dot |
| `{{height_dots}}` | Configured media height in printer dots | Printer height in millimeters × DPI ÷ 25.4, rounded to the nearest dot |
| `{{dpi}}` | Printer resolution as an integer | The selected printer's DPI setting |
| `{{city}}` | Destination city | City field in the shipping-label form |
| `{{recipient}}` | Recipient or office | Recipient / office field in the shipping-label form |
| `{{address}}` | Destination address | Address field in the shipping-label form |
| `{{phone}}` | Contact telephone number | Phone field in the shipping-label form |
| `{{asset_name}}` | Asset name | Prefilled from the linked GLPI asset or ticket and editable before printing |
| `{{asset_type}}` | GLPI item type | Prefilled from the linked GLPI record and editable before printing |
| `{{inventory_number}}` | Inventory number | Prefilled from the linked asset when available; editable before printing |
| `{{serial_number}}` | Serial number | Prefilled from the linked asset when available; editable before printing |
| `{{notes}}` | Additional notes | Notes field in the shipping-label form |

Empty source fields become empty text. If a field should not print when empty, remove that field's ZPL line from the template; the template format has no conditional or branching tags. Placeholder names are case-sensitive; unsupported or malformed placeholders cause the print request to be rejected.

## Minimal example

This example prints a centered recipient heading and an address. Coordinates and font sizes are in printer dots, so adjust them for the configured label size and DPI.

```zpl
^XA
^PW{{width_dots}}
^LL{{height_dots}}
^CI28
^CWX,E:TT0003M_.FNT^FS
^FO0,24^AXN,48,48^FB{{width_dots}},1,0,C,0^FD{{recipient}}^FS
^FO0,84^AXN,30,30^FB{{width_dots}},2,4,L,0^FDАдрес: {{address}}^FS
^FO0,164^AXN,22,22^FB{{width_dots}},2,4,L,0^FDАктив: {{asset_name}}^FS
^XZ
```

`^CI28` selects UTF-8 input. `^CWX,E:TT0003M_.FNT` assigns the printer's installed TrueType font to alias `X`; availability and font coverage can vary by model. If that font is not installed, use a font supported by the target printer. This is only a starting point: test Cyrillic, alignment, line wrapping, and clipping on the actual printer and media before operational use.

## Editing and testing safely

1. Start from a backup of the current template or download a recovery export before making changes.
2. Keep `^XA` as the first command and `^XZ` as the last command. The editor accepts templates up to 16 KiB.
3. Add placeholders only inside the ZPL field where their text should print, commonly between `^FD` and `^FS`.
4. Use `{{width_dots}}`, `{{height_dots}}`, and `{{dpi}}` to adapt positions or print width to the selected printer. `^FO` positions and font dimensions are still your responsibility.
5. Save, print a test label, inspect the physical result, and adjust the layout. The service does not calculate whether custom text will fit or automatically resize the layout.
6. If the result is unusable, restore the previous template or clear the editor to return to the built-in layout.

Values substituted from GLPI have ZPL command markers (`^` and `~`) and ASCII control characters removed, line breaks replaced with spaces, and text limited to 180 characters. This protects inserted record values from being interpreted as ZPL commands; it does not sanitize the template itself. Custom ZPL commands are passed to the configured printer. Only trusted GLPI administrators should edit templates, and printer-specific commands can move, erase, or otherwise change label output.

## Български

Персонализираните ZPL шаблони са функция на **Professional** версията. Необходим е валиден Professional лиценз както за записване на шаблона в GLPI, така и за отпечатването му чрез Print Service. В демо режима редакторът се вижда като заключен преглед, използва се вграденият шаблон и демо етикетите запазват надписа `DEMO`.

Отворете **Настройка → Плъгини → GLPI Zebra Shipping** и намерете **Custom ZPL label template**. Поставете пълен ZPL формат и натиснете **Save ZPL template**. За връщане към стандартния етикет изтрийте съдържанието и запишете празното поле с активен Professional лиценз. Това не изтрива настройките на принтерите или историята на печата.

### Тагове и данни

| Таг | Какво се отпечатва | Източник |
| --- | --- | --- |
| `{{width_dots}}` | Ширина на носителя в точки на принтера | Ширина в mm × DPI ÷ 25,4, закръглена до най-близката точка |
| `{{height_dots}}` | Височина на носителя в точки на принтера | Височина в mm × DPI ÷ 25,4, закръглена до най-близката точка |
| `{{dpi}}` | Резолюция на принтера | Настройката DPI на избрания принтер |
| `{{city}}` | Град | Полето за град във формата за етикет |
| `{{recipient}}` | Получател или офис | Полето Recipient / office във формата |
| `{{address}}` | Адрес | Полето Address във формата |
| `{{phone}}` | Телефон | Полето Phone във формата |
| `{{asset_name}}` | Име на актива | Попълва се от свързания GLPI актив или заявка и може да се редактира преди печат |
| `{{asset_type}}` | Тип на GLPI обекта | Попълва се от GLPI записа и може да се редактира преди печат |
| `{{inventory_number}}` | Инвентарен номер | Попълва се от актива, ако е наличен; може да се редактира преди печат |
| `{{serial_number}}` | Сериен номер | Попълва се от актива, ако е наличен; може да се редактира преди печат |
| `{{notes}}` | Допълнителни бележки | Полето Notes във формата за етикет |

Таговете се изписват точно с малки букви и двойни фигурни скоби. Празно поле се заменя с празен текст. Неподдържан или неправилно изписан таг води до отказ на заявката за печат.

### Пример и безопасна редакция

Използвайте пълен шаблон от `^XA` до `^XZ`. Позициите и размерите на шрифта в ZPL са в точки на принтера; те не се преоразмеряват автоматично. Започнете от резервно копие, поставяйте таговете в текстови команди като `^FD...^FS`, запишете шаблона и проверете физически отпечатания етикет на целевия принтер. При проблем върнете копието или запишете празен шаблон, за да се използва вграденият дизайн.

Стойностите от GLPI се обработват преди заместване: символите за ZPL команди `^` и `~` и ASCII контролни знаци се премахват, новите редове стават интервали, а текстът се ограничава до 180 знака. Самият шаблон не се филтрира като недоверен текст; неговите ZPL команди се изпращат към конфигурирания принтер. Редактирайте шаблона само с доверен GLPI администратор.
