#!/bin/bash

# MCP Proxy Server startup script

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Default values
CONFIG_FILE="mcp_servers.json"
NODE_ENV="production"
LOG_LEVEL="info"

# Parse command line arguments
while [[ $# -gt 0 ]]; do
  case $1 in
    -c|--config)
      CONFIG_FILE="$2"
      shift 2
      ;;
    -e|--env)
      NODE_ENV="$2"
      shift 2
      ;;
    -l|--log-level)
      LOG_LEVEL="$2"
      shift 2
      ;;
    -h|--help)
      echo "Usage: $0 [OPTIONS]"
      echo "Options:"
      echo "  -c, --config FILE     Configuration file (default: mcp_servers.json)"
      echo "  -e, --env ENV         Node environment (default: production)"
      echo "  -l, --log-level LEVEL Log level (default: info)"
      echo "  -h, --help           Show this help"
      exit 0
      ;;
    *)
      echo "Unknown option: $1"
      exit 1
      ;;
  esac
done

echo -e "${GREEN}Starting MCP Proxy Server...${NC}"

# Check if Node.js is installed
if ! command -v node &> /dev/null; then
    echo -e "${RED}Error: Node.js is not installed${NC}"
    exit 1
fi

# Check Node.js version
NODE_VERSION=$(node --version | cut -d'v' -f2)
NODE_MAJOR_VERSION=$(echo $NODE_VERSION | cut -d'.' -f1)

if [ "$NODE_MAJOR_VERSION" -lt 18 ]; then
    echo -e "${RED}Error: Node.js version 18 or higher is required (current: $NODE_VERSION)${NC}"
    exit 1
fi

# Check if configuration file exists
if [ ! -f "$CONFIG_FILE" ]; then
    echo -e "${RED}Error: Configuration file not found: $CONFIG_FILE${NC}"
    exit 1
fi

# Check if built files exist
if [ ! -d "dist" ]; then
    echo -e "${YELLOW}Warning: Built files not found. Building project...${NC}"
    npm run build
fi

# Set environment variables
export NODE_ENV="$NODE_ENV"
export MCP_PROXY_CONFIG="$CONFIG_FILE"

echo -e "${GREEN}Configuration:${NC}"
echo "  Config file: $CONFIG_FILE"
echo "  Environment: $NODE_ENV"
echo "  Log level: $LOG_LEVEL"
echo ""

# Start the server
echo -e "${GREEN}Starting server...${NC}"
exec node dist/index.js