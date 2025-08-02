# MCP Proxy Server Dockerfile

FROM node:18-alpine

# Set working directory
WORKDIR /app

# Copy package files
COPY package*.json ./
COPY tsconfig.json ./

# Install dependencies
RUN npm ci --only=production

# Copy source code
COPY src/ ./src/
COPY scripts/ ./scripts/

# Build TypeScript
RUN npm run build

# Create non-root user
RUN addgroup -g 1001 -S mcpproxy && \
    adduser -S mcpproxy -u 1001

# Change ownership of app directory
RUN chown -R mcpproxy:mcpproxy /app

# Switch to non-root user
USER mcpproxy

# Expose port (configurable via environment)
EXPOSE 3000

# Health check
HEALTHCHECK --interval=30s --timeout=10s --start-period=5s --retries=3 \
  CMD node -e "const http = require('http'); \
    const req = http.request({hostname: 'localhost', port: process.env.MCP_PROXY_PORT || 3000, path: '/health'}, (res) => { \
      process.exit(res.statusCode === 200 ? 0 : 1); \
    }); \
    req.on('error', () => process.exit(1)); \
    req.end();"

# Default command
CMD ["node", "dist/index.js"]

# Build args for metadata
ARG BUILD_DATE
ARG VCS_REF
ARG VERSION

# Labels for metadata
LABEL org.label-schema.build-date=$BUILD_DATE \
      org.label-schema.name="mcp-proxy-server" \
      org.label-schema.description="TypeScript MCP Proxy Server with HTTP streaming transport" \
      org.label-schema.vcs-ref=$VCS_REF \
      org.label-schema.vcs-url="https://github.com/example/mcp-proxy" \
      org.label-schema.version=$VERSION \
      org.label-schema.schema-version="1.0"