using HeyDav.Domain.Common.Base;
using HeyDav.Domain.AgentManagement.ValueObjects;
using HeyDav.Domain.AgentManagement.Events;

namespace HeyDav.Domain.AgentManagement.Entities;

public class McpServer : AggregateRoot
{
    private readonly List<string> _supportedTools = new();
    private readonly Dictionary<string, object> _metadata = new();

    public string Name { get; private set; }
    public string? Description { get; private set; }
    public McpServerEndpoint Endpoint { get; private set; }
    public bool IsActive { get; private set; }
    public DateTime? LastConnectedAt { get; private set; }
    public DateTime? LastHealthCheckAt { get; private set; }
    public string? LastError { get; private set; }
    public string? Version { get; private set; }
    public int SuccessfulRequestsCount { get; private set; }
    public int FailedRequestsCount { get; private set; }
    public double AverageResponseTime { get; private set; }
    public IReadOnlyList<string> SupportedTools => _supportedTools.AsReadOnly();
    public IReadOnlyDictionary<string, object> Metadata => _metadata.AsReadOnly();

    private McpServer()
    {
        // EF Constructor
        Name = string.Empty;
        Endpoint = McpServerEndpoint.Create("default", "http", "localhost", 8080);
        IsActive = false;
        SuccessfulRequestsCount = 0;
        FailedRequestsCount = 0;
        AverageResponseTime = 0.0;
    }

    private McpServer(
        string name,
        McpServerEndpoint endpoint,
        string? description = null)
    {
        Name = name;
        Endpoint = endpoint;
        Description = description;
        IsActive = false;
        SuccessfulRequestsCount = 0;
        FailedRequestsCount = 0;
        AverageResponseTime = 0.0;
    }

    public static McpServer Create(
        string name,
        McpServerEndpoint endpoint,
        string? description = null)
    {
        if (string.IsNullOrWhiteSpace(name))
            throw new ArgumentException("MCP Server name cannot be empty", nameof(name));

        if (endpoint == null)
            throw new ArgumentNullException(nameof(endpoint));

        var server = new McpServer(name, endpoint, description);
        server.AddDomainEvent(new McpServerCreatedEvent(server.Id, name, endpoint.GetConnectionString()));
        return server;
    }

    public void UpdateName(string name)
    {
        if (string.IsNullOrWhiteSpace(name))
            throw new ArgumentException("MCP Server name cannot be empty", nameof(name));

        Name = name;
        UpdateTimestamp();
    }

    public void UpdateDescription(string? description)
    {
        Description = description;
        UpdateTimestamp();
    }

    public void UpdateEndpoint(McpServerEndpoint endpoint)
    {
        Endpoint = endpoint ?? throw new ArgumentNullException(nameof(endpoint));
        UpdateTimestamp();
        AddDomainEvent(new McpServerEndpointUpdatedEvent(Id, endpoint.GetConnectionString()));
    }

    public void Connect(string? version = null, IEnumerable<string>? supportedTools = null)
    {
        IsActive = true;
        LastConnectedAt = DateTime.UtcNow;
        LastError = null;
        Version = version;

        if (supportedTools != null)
        {
            _supportedTools.Clear();
            _supportedTools.AddRange(supportedTools);
        }

        UpdateTimestamp();
        AddDomainEvent(new McpServerConnectedEvent(Id, version, supportedTools?.ToList()));
    }

    public void Disconnect()
    {
        IsActive = false;
        UpdateTimestamp();
        AddDomainEvent(new McpServerDisconnectedEvent(Id));
    }

    public void SetError(string error)
    {
        if (string.IsNullOrWhiteSpace(error))
            throw new ArgumentException("Error message cannot be empty", nameof(error));

        IsActive = false;
        LastError = error;
        UpdateTimestamp();
        AddDomainEvent(new McpServerErrorEvent(Id, error));
    }

    public void RecordHealthCheck(bool isHealthy)
    {
        LastHealthCheckAt = DateTime.UtcNow;
        
        if (isHealthy && !IsActive)
        {
            IsActive = true;
            LastError = null;
        }
        else if (!isHealthy && IsActive)
        {
            IsActive = false;
        }
        
        UpdateTimestamp();
    }

    public void AddSupportedTool(string tool)
    {
        if (string.IsNullOrWhiteSpace(tool))
            throw new ArgumentException("Tool name cannot be empty", nameof(tool));

        if (!_supportedTools.Contains(tool))
        {
            _supportedTools.Add(tool);
            UpdateTimestamp();
        }
    }

    public void RemoveSupportedTool(string tool)
    {
        if (_supportedTools.Remove(tool))
        {
            UpdateTimestamp();
        }
    }

    public void AddMetadata(string key, object value)
    {
        if (string.IsNullOrWhiteSpace(key))
            throw new ArgumentException("Metadata key cannot be empty", nameof(key));

        _metadata[key] = value;
        UpdateTimestamp();
    }

    public void RemoveMetadata(string key)
    {
        if (_metadata.Remove(key))
        {
            UpdateTimestamp();
        }
    }

    public void RecordSuccessfulRequest(TimeSpan responseTime)
    {
        SuccessfulRequestsCount++;
        UpdateAverageResponseTime(responseTime);
        UpdateTimestamp();
    }

    public void RecordFailedRequest()
    {
        FailedRequestsCount++;
        UpdateTimestamp();
    }

    public bool SupportsTool(string tool)
    {
        return _supportedTools.Contains(tool);
    }

    public double GetSuccessRate()
    {
        var totalRequests = SuccessfulRequestsCount + FailedRequestsCount;
        return totalRequests == 0 ? 0.0 : (double)SuccessfulRequestsCount / totalRequests;
    }

    public bool IsHealthy()
    {
        return IsActive && string.IsNullOrEmpty(LastError);
    }

    private void UpdateAverageResponseTime(TimeSpan newResponseTime)
    {
        if (SuccessfulRequestsCount == 1)
        {
            AverageResponseTime = newResponseTime.TotalMilliseconds;
        }
        else
        {
            AverageResponseTime = (AverageResponseTime * (SuccessfulRequestsCount - 1) + 
                                 newResponseTime.TotalMilliseconds) / SuccessfulRequestsCount;
        }
    }
}