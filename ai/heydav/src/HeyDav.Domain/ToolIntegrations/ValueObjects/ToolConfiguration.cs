using HeyDav.Domain.Common.Base;

namespace HeyDav.Domain.ToolIntegrations.ValueObjects;

public class ToolConfiguration : ValueObject
{
    public Dictionary<string, string> Settings { get; private set; }
    public Dictionary<string, string> Headers { get; private set; }
    public Dictionary<string, string> QueryParameters { get; private set; }
    public int TimeoutSeconds { get; private set; }
    public int MaxRetries { get; private set; }
    public bool VerifySsl { get; private set; }
    public string UserAgent { get; private set; }

    public ToolConfiguration()
    {
        Settings = new Dictionary<string, string>();
        Headers = new Dictionary<string, string>();
        QueryParameters = new Dictionary<string, string>();
        TimeoutSeconds = 30;
        MaxRetries = 3;
        VerifySsl = true;
        UserAgent = "HeyDav/1.0";
    }

    public ToolConfiguration(
        Dictionary<string, string>? settings = null,
        Dictionary<string, string>? headers = null,
        Dictionary<string, string>? queryParameters = null,
        int timeoutSeconds = 30,
        int maxRetries = 3,
        bool verifySsl = true,
        string userAgent = "HeyDav/1.0")
    {
        Settings = settings ?? new Dictionary<string, string>();
        Headers = headers ?? new Dictionary<string, string>();
        QueryParameters = queryParameters ?? new Dictionary<string, string>();
        TimeoutSeconds = Math.Max(1, timeoutSeconds);
        MaxRetries = Math.Max(0, maxRetries);
        VerifySsl = verifySsl;
        UserAgent = userAgent ?? "HeyDav/1.0";
    }

    public string? GetSetting(string key)
    {
        return Settings.TryGetValue(key, out var value) ? value : null;
    }

    public string? GetHeader(string key)
    {
        return Headers.TryGetValue(key, out var value) ? value : null;
    }

    public string? GetQueryParameter(string key)
    {
        return QueryParameters.TryGetValue(key, out var value) ? value : null;
    }

    public ToolConfiguration WithSetting(string key, string value)
    {
        var newSettings = new Dictionary<string, string>(Settings)
        {
            [key] = value
        };
        
        return new ToolConfiguration(
            newSettings,
            Headers,
            QueryParameters,
            TimeoutSeconds,
            MaxRetries,
            VerifySsl,
            UserAgent);
    }

    public ToolConfiguration WithHeader(string key, string value)
    {
        var newHeaders = new Dictionary<string, string>(Headers)
        {
            [key] = value
        };
        
        return new ToolConfiguration(
            Settings,
            newHeaders,
            QueryParameters,
            TimeoutSeconds,
            MaxRetries,
            VerifySsl,
            UserAgent);
    }

    public ToolConfiguration WithQueryParameter(string key, string value)
    {
        var newQueryParameters = new Dictionary<string, string>(QueryParameters)
        {
            [key] = value
        };
        
        return new ToolConfiguration(
            Settings,
            Headers,
            newQueryParameters,
            TimeoutSeconds,
            MaxRetries,
            VerifySsl,
            UserAgent);
    }

    protected override IEnumerable<object> GetEqualityComponents()
    {
        yield return Settings;
        yield return Headers;
        yield return QueryParameters;
        yield return TimeoutSeconds;
        yield return MaxRetries;
        yield return VerifySsl;
        yield return UserAgent;
    }
}