using HeyDav.Domain.Common.Base;

namespace HeyDav.Domain.AgentManagement.ValueObjects;

public class McpServerEndpoint : ValueObject
{
    public string Name { get; }
    public string Protocol { get; }
    public string Host { get; }
    public int Port { get; }
    public string? Path { get; }
    public Dictionary<string, string> Headers { get; }
    public bool RequiresAuthentication { get; }

    private McpServerEndpoint(
        string name,
        string protocol,
        string host,
        int port,
        string? path,
        Dictionary<string, string> headers,
        bool requiresAuthentication)
    {
        Name = name;
        Protocol = protocol;
        Host = host;
        Port = port;
        Path = path;
        Headers = headers;
        RequiresAuthentication = requiresAuthentication;
    }

    public static McpServerEndpoint Create(
        string name,
        string protocol,
        string host,
        int port,
        string? path = null,
        Dictionary<string, string>? headers = null,
        bool requiresAuthentication = false)
    {
        if (string.IsNullOrWhiteSpace(name))
            throw new ArgumentException("Name cannot be empty", nameof(name));

        if (string.IsNullOrWhiteSpace(protocol))
            throw new ArgumentException("Protocol cannot be empty", nameof(protocol));

        if (string.IsNullOrWhiteSpace(host))
            throw new ArgumentException("Host cannot be empty", nameof(host));

        if (port <= 0 || port > 65535)
            throw new ArgumentException("Port must be between 1 and 65535", nameof(port));

        if (!IsValidProtocol(protocol))
            throw new ArgumentException("Invalid protocol. Supported: http, https, ws, wss", nameof(protocol));

        return new McpServerEndpoint(
            name,
            protocol.ToLowerInvariant(),
            host,
            port,
            path,
            headers ?? new Dictionary<string, string>(),
            requiresAuthentication);
    }

    public string GetConnectionString()
    {
        var baseUrl = $"{Protocol}://{Host}:{Port}";
        return string.IsNullOrEmpty(Path) ? baseUrl : $"{baseUrl}/{Path.TrimStart('/')}";
    }

    private static bool IsValidProtocol(string protocol)
    {
        var validProtocols = new[] { "http", "https", "ws", "wss" };
        return validProtocols.Contains(protocol.ToLowerInvariant());
    }

    protected override IEnumerable<object> GetEqualityComponents()
    {
        yield return Name;
        yield return Protocol;
        yield return Host;
        yield return Port;
        yield return Path ?? string.Empty;
        yield return RequiresAuthentication;
        
        foreach (var header in Headers.OrderBy(x => x.Key))
        {
            yield return header.Key;
            yield return header.Value;
        }
    }
}