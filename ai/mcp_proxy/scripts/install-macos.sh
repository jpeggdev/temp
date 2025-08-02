#!/bin/bash

# Installation script for macOS using launchd

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_DIR="$(dirname "$SCRIPT_DIR")"
PLIST_NAME="com.mcp-proxy"
PLIST_FILE="$PROJECT_DIR/$PLIST_NAME.plist"
LAUNCH_AGENTS_DIR="$HOME/Library/LaunchAgents"
USER_HOME="$HOME"

echo "Installing MCP Proxy Server as macOS launch agent..."

# Check if running on macOS
if [[ "$(uname)" != "Darwin" ]]; then
    echo "Error: This script is for macOS only."
    exit 1
fi

# Build the project
echo "Building the project..."
cd "$PROJECT_DIR"
npm install
npm run build

# Create logs directory
echo "Creating logs directory..."
mkdir -p "$HOME/Library/Logs/mcp-proxy"

# Create LaunchAgents directory if it doesn't exist
mkdir -p "$LAUNCH_AGENTS_DIR"

# Update plist file with actual paths
echo "Configuring launch agent..."
sed "s|USER_HOME|$USER_HOME|g" "$PLIST_FILE" > "$LAUNCH_AGENTS_DIR/$PLIST_NAME.plist"

# Load the launch agent
echo "Loading launch agent..."
launchctl load "$LAUNCH_AGENTS_DIR/$PLIST_NAME.plist"

# Start the service
echo "Starting MCP Proxy service..."
launchctl start "$PLIST_NAME"

echo ""
echo "Installation complete!"
echo ""
echo "The MCP Proxy Server has been installed as a macOS launch agent."
echo "It will start automatically when you log in."
echo ""
echo "Useful commands:"
echo "  Check status:  launchctl list | grep mcp-proxy"
echo "  Start service: launchctl start $PLIST_NAME"
echo "  Stop service:  launchctl stop $PLIST_NAME"
echo "  View logs:     tail -f ~/Library/Logs/mcp-proxy/*.log"
echo ""
echo "Web interface: http://localhost:3001"
echo "Default password: 'admin' (change this in ~/Library/LaunchAgents/$PLIST_NAME.plist)"
echo ""
echo "To change the password:"
echo "  1. Edit ~/Library/LaunchAgents/$PLIST_NAME.plist"
echo "  2. Unload: launchctl unload ~/Library/LaunchAgents/$PLIST_NAME.plist"
echo "  3. Load: launchctl load ~/Library/LaunchAgents/$PLIST_NAME.plist"