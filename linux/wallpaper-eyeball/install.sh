#!/bin/bash

# Installation script for Eyeball Wallpaper Plugin for KDE Plasma 6

PLUGIN_NAME="plasma-eyeball-wallpaper"
PLUGIN_DIR="$(dirname "$(readlink -f "$0")")/$PLUGIN_NAME"

# Check if the plugin directory exists
if [ ! -d "$PLUGIN_DIR" ]; then
    echo "Error: Plugin directory not found at $PLUGIN_DIR"
    exit 1
fi

# Determine installation directory
if [ -n "$XDG_DATA_HOME" ]; then
    INSTALL_DIR="$XDG_DATA_HOME/plasma/wallpapers"
else
    INSTALL_DIR="$HOME/.local/share/plasma/wallpapers"
fi

# Create installation directory if it doesn't exist
mkdir -p "$INSTALL_DIR"

# Copy the plugin
echo "Installing $PLUGIN_NAME to $INSTALL_DIR..."
cp -r "$PLUGIN_DIR" "$INSTALL_DIR/"

# Check if installation was successful
if [ $? -eq 0 ]; then
    echo "Installation successful!"
    echo ""
    echo "To use the wallpaper:"
    echo "1. Right-click on your desktop"
    echo "2. Select 'Configure Desktop and Wallpaper' or press Alt+D, Alt+S"
    echo "3. Choose 'Eyeball Wallpaper' from the wallpaper type list"
    echo ""
    echo "Note: You may need to restart plasmashell for the wallpaper to appear:"
    echo "  systemctl --user restart plasma-plasmashell.service"
    echo "  or"
    echo "  kquitapp5 plasmashell && kstart5 plasmashell"
else
    echo "Error: Installation failed!"
    exit 1
fi