# Eyeball Wallpaper Plugin for KDE Plasma 6

A fun and interactive wallpaper plugin for KDE Plasma 6 that displays a large eyeball that follows your mouse cursor around the screen.

## Features

- Large eyeball that tracks mouse movement
- Customizable colors (background and iris)
- Realistic blinking animation
- Optional eyelids
- Smooth eye movement with natural constraints

## Requirements

- KDE Plasma 6
- Qt 6
- QML

## Installation

### Method 1: Using the install script

1. Clone or download this repository
2. Run the installation script:
   ```bash
   ./install.sh
   ```

### Method 2: Manual installation

1. Copy the `plasma-eyeball-wallpaper` directory to your wallpapers folder:
   ```bash
   cp -r plasma-eyeball-wallpaper ~/.local/share/plasma/wallpapers/
   ```

2. Restart plasmashell:
   ```bash
   systemctl --user restart plasma-plasmashell.service
   ```
   or
   ```bash
   kquitapp5 plasmashell && kstart5 plasmashell
   ```

## Usage

1. Right-click on your desktop
2. Select "Configure Desktop and Wallpaper" (or press Alt+D, Alt+S)
3. Choose "Eyeball Wallpaper" from the wallpaper type dropdown
4. Configure the settings as desired:
   - Background Color: The color behind the eyeball
   - Iris Color: The color of the iris
   - Enable blinking: Toggle the blinking animation
   - Show eyelids: Toggle the visibility of eyelids

## Configuration Options

- **Background Color**: Set the background color using hex format (#RRGGBB)
- **Iris Color**: Set the iris color using hex format (#RRGGBB)
- **Enable Blinking**: Turn the blinking animation on/off
- **Show Eyelids**: Display or hide the eyelids

## Troubleshooting

If the wallpaper doesn't appear in the list:
1. Make sure you've installed it in the correct directory
2. Restart plasmashell
3. Check that you're running KDE Plasma 6

## License

GPL-2.0+

## Contributing

Feel free to submit issues, feature requests, or pull requests to improve the eyeball wallpaper!