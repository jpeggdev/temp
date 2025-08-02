namespace HeyDav.Application.TaskProcessing;

public interface IPluginManager
{
    Task<List<IPlugin>> GetLoadedPluginsAsync();
    Task<IPlugin?> GetPluginByIdAsync(string pluginId);
    Task<bool> LoadPluginAsync(string pluginPath);
    Task<bool> UnloadPluginAsync(string pluginId);
    Task<bool> ReloadPluginAsync(string pluginId);
    Task<List<PluginInfo>> DiscoverPluginsAsync(string directory);
    Task<PluginLoadResult> LoadPluginFromAssemblyAsync(string assemblyPath);
    Task ValidatePluginAsync(IPlugin plugin);
    event EventHandler<PluginLoadedEventArgs> PluginLoaded;
    event EventHandler<PluginUnloadedEventArgs> PluginUnloaded;
    event EventHandler<PluginErrorEventArgs> PluginError;
}

public interface IPlugin
{
    string Id { get; }
    string Name { get; }
    string Version { get; }
    string Description { get; }
    PluginMetadata Metadata { get; }
    Task<bool> InitializeAsync(IServiceProvider serviceProvider);
    Task<bool> StartAsync();
    Task<bool> StopAsync();
    Task<bool> UnloadAsync();
    Task<PluginCapabilities> GetCapabilitiesAsync();
    Task<object?> ExecuteAsync(string command, Dictionary<string, object>? parameters = null);
    bool IsCompatible(string frameworkVersion);
}

public class PluginInfo
{
    public string Id { get; set; } = string.Empty;
    public string Name { get; set; } = string.Empty;
    public string Version { get; set; } = string.Empty;
    public string Description { get; set; } = string.Empty;
    public string AssemblyPath { get; set; } = string.Empty;
    public string ConfigurationPath { get; set; } = string.Empty;
    public PluginMetadata Metadata { get; set; } = new();
    public PluginStatus Status { get; set; }
    public DateTime? LoadedAt { get; set; }
    public List<string> Dependencies { get; set; } = new();
    public Dictionary<string, object> Configuration { get; set; } = new();
}

public class PluginMetadata
{
    public string Author { get; set; } = string.Empty;
    public string Website { get; set; } = string.Empty;
    public string License { get; set; } = string.Empty;
    public List<string> Tags { get; set; } = new();
    public string MinFrameworkVersion { get; set; } = string.Empty;
    public string MaxFrameworkVersion { get; set; } = string.Empty;
    public List<PluginDependency> Dependencies { get; set; } = new();
    public Dictionary<string, object> CustomProperties { get; set; } = new();
    public List<string> RequiredPermissions { get; set; } = new();
    public PluginSandboxSettings Sandbox { get; set; } = new();
}

public class PluginDependency
{
    public string Id { get; set; } = string.Empty;
    public string MinVersion { get; set; } = string.Empty;
    public string MaxVersion { get; set; } = string.Empty;
    public bool Optional { get; set; }
}

public class PluginSandboxSettings
{
    public bool EnableSandbox { get; set; } = true;
    public List<string> AllowedNamespaces { get; set; } = new();
    public List<string> RestrictedNamespaces { get; set; } = new();
    public bool AllowFileAccess { get; set; } = false;
    public bool AllowNetworkAccess { get; set; } = false;
    public bool AllowRegistryAccess { get; set; } = false;
    public Dictionary<string, object> ResourceLimits { get; set; } = new();
}

public class PluginCapabilities
{
    public List<string> SupportedCommands { get; set; } = new();
    public List<string> ProvidedServices { get; set; } = new();
    public List<string> ConsumedServices { get; set; } = new();
    public bool SupportsHotReload { get; set; }
    public bool SupportsConfiguration { get; set; }
    public Dictionary<string, object> ExtensionPoints { get; set; } = new();
}

public class PluginLoadResult
{
    public bool Success { get; set; }
    public string Message { get; set; } = string.Empty;
    public IPlugin? Plugin { get; set; }
    public List<string> Errors { get; set; } = new();
    public List<string> Warnings { get; set; } = new();
    public TimeSpan LoadTime { get; set; }
}

public enum PluginStatus
{
    Discovered,
    Loading,
    Loaded,
    Running,
    Stopped,
    Unloading,
    Unloaded,
    Failed,
    Disabled
}

public class PluginLoadedEventArgs : EventArgs
{
    public IPlugin Plugin { get; set; } = null!;
    public TimeSpan LoadTime { get; set; }
}

public class PluginUnloadedEventArgs : EventArgs
{
    public string PluginId { get; set; } = string.Empty;
    public string PluginName { get; set; } = string.Empty;
}

public class PluginErrorEventArgs : EventArgs
{
    public string PluginId { get; set; } = string.Empty;
    public Exception Exception { get; set; } = null!;
    public string ErrorMessage { get; set; } = string.Empty;
}