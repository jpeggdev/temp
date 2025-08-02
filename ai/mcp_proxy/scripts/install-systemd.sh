#!/bin/bash

# Installation script for systemd-based Linux systems

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_DIR="$(dirname "$SCRIPT_DIR")"
SERVICE_NAME="mcp-proxy@$USER"

echo "Installing MCP Proxy Server as systemd service..."

# Check if running on Linux with systemd
if ! command -v systemctl &> /dev/null; then
    echo "Error: systemd not found. This script is for systemd-based Linux systems."
    exit 1
fi

# Build the project
echo "Building the project..."
cd "$PROJECT_DIR"
npm install
npm run build

# Copy service file
echo "Installing systemd service file..."
sudo cp "$PROJECT_DIR/mcp-proxy.service" "/etc/systemd/system/mcp-proxy@.service"

# Reload systemd
echo "Reloading systemd daemon..."
sudo systemctl daemon-reload

# Enable the service
echo "Enabling MCP Proxy service for user $USER..."
sudo systemctl enable "$SERVICE_NAME"

# Start the service
echo "Starting MCP Proxy service..."
sudo systemctl start "$SERVICE_NAME"

# Check status
echo "Checking service status..."
sudo systemctl status "$SERVICE_NAME" --no-pager

echo ""
echo "Installation complete!"
echo ""
echo "The MCP Proxy Server has been installed as a systemd service."
echo "It will start automatically on system boot."
echo ""
echo "Useful commands:"
echo "  Check status:  sudo systemctl status $SERVICE_NAME"
echo "  Start service: sudo systemctl start $SERVICE_NAME"
echo "  Stop service:  sudo systemctl stop $SERVICE_NAME"
echo "  View logs:     sudo journalctl -u $SERVICE_NAME -f"
echo ""
echo "Web interface: http://localhost:3001"
echo "Default password: 'admin' (change this in /etc/systemd/system/mcp-proxy@.service)"
echo ""
echo "To change the password, edit the service file and restart:"
echo "  sudo systemctl edit $SERVICE_NAME"
echo "  sudo systemctl restart $SERVICE_NAME"