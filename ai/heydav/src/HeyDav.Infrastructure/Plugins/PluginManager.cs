using Microsoft.Extensions.Logging;
using System.Reflection;
using System.Runtime.Loader;
using HeyDav.Application.TaskProcessing;

namespace HeyDav.Infrastructure.Plugins;

public class PluginManager : IPluginManager
{
    private readonly ILogger<PluginManager> _logger;
    private readonly Dictionary<string, IPlugin> _loadedPlugins = new();
    private readonly Dictionary<string, PluginLoadContext> _pluginContexts = new();
    private readonly Dictionary<string, PluginInfo> _pluginInfos = new();

    public event EventHandler<PluginLoadedEventArgs>? PluginLoaded;
    public event EventHandler<PluginUnloadedEventArgs>? PluginUnloaded;
    public event EventHandler<PluginErrorEventArgs>? PluginError;

    public PluginManager(ILogger<PluginManager> logger)
    {
        _logger = logger;
    }

    public async Task<List<IPlugin>> GetLoadedPluginsAsync()
    {
        return _loadedPlugins.Values.ToList();
    }

    public async Task<IPlugin?> GetPluginByIdAsync(string pluginId)
    {
        _loadedPlugins.TryGetValue(pluginId, out var plugin);
        return plugin;
    }

    public async Task<bool> LoadPluginAsync(string pluginPath)
    {
        try
        {
            _logger.LogInformation("Loading plugin from path: {PluginPath}", pluginPath);

            if (!File.Exists(pluginPath))
            {
                _logger.LogError("Plugin assembly not found: {PluginPath}", pluginPath);
                return false;
            }

            var result = await LoadPluginFromAssemblyAsync(pluginPath);
            return result.Success;
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Error loading plugin from path: {PluginPath}", pluginPath);
            OnPluginError(new PluginErrorEventArgs { PluginId = pluginPath, Exception = ex, ErrorMessage = ex.Message });
            return false;
        }
    }

    public async Task<bool> UnloadPluginAsync(string pluginId)
    {
        try
        {
            _logger.LogInformation("Unloading plugin: {PluginId}", pluginId);

            if (!_loadedPlugins.TryGetValue(pluginId, out var plugin))
            {
                _logger.LogWarning("Plugin not found for unloading: {PluginId}", pluginId);
                return false;
            }

            // Stop and unload the plugin
            await plugin.StopAsync();
            await plugin.UnloadAsync();

            // Remove from collections
            _loadedPlugins.Remove(pluginId);
            _pluginInfos.Remove(pluginId);

            // Unload the plugin context
            if (_pluginContexts.TryGetValue(pluginId, out var context))
            {
                context.Unload();
                _pluginContexts.Remove(pluginId);
            }

            OnPluginUnloaded(new PluginUnloadedEventArgs 
            { 
                PluginId = pluginId, 
                PluginName = plugin.Name 
            });

            _logger.LogInformation("Plugin unloaded successfully: {PluginId}", pluginId);
            return true;
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Error unloading plugin: {PluginId}", pluginId);
            OnPluginError(new PluginErrorEventArgs { PluginId = pluginId, Exception = ex, ErrorMessage = ex.Message });
            return false;
        }
    }

    public async Task<bool> ReloadPluginAsync(string pluginId)
    {
        try
        {
            _logger.LogInformation("Reloading plugin: {PluginId}", pluginId);

            if (!_pluginInfos.TryGetValue(pluginId, out var pluginInfo))
            {
                _logger.LogError("Plugin info not found for reloading: {PluginId}", pluginId);
                return false;
            }

            // Unload the current plugin
            await UnloadPluginAsync(pluginId);

            // Wait a moment for cleanup
            await Task.Delay(100);

            // Reload from the original assembly path
            return await LoadPluginAsync(pluginInfo.AssemblyPath);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Error reloading plugin: {PluginId}", pluginId);
            OnPluginError(new PluginErrorEventArgs { PluginId = pluginId, Exception = ex, ErrorMessage = ex.Message });
            return false;
        }
    }

    public async Task<List<PluginInfo>> DiscoverPluginsAsync(string directory)
    {
        var discoveredPlugins = new List<PluginInfo>();

        try
        {
            _logger.LogInformation("Discovering plugins in directory: {Directory}", directory);

            if (!Directory.Exists(directory))
            {
                _logger.LogWarning("Plugin directory does not exist: {Directory}", directory);
                return discoveredPlugins;
            }

            var assemblyFiles = Directory.GetFiles(directory, "*.dll", SearchOption.AllDirectories);
            _logger.LogDebug("Found {AssemblyCount} assembly files", assemblyFiles.Length);

            foreach (var assemblyFile in assemblyFiles)
            {
                try
                {
                    var pluginInfo = await AnalyzeAssemblyAsync(assemblyFile);
                    if (pluginInfo != null)
                    {
                        discoveredPlugins.Add(pluginInfo);
                        _logger.LogDebug("Discovered plugin: {PluginName} v{Version}", pluginInfo.Name, pluginInfo.Version);
                    }
                }
                catch (Exception ex)
                {
                    _logger.LogWarning(ex, "Failed to analyze assembly: {AssemblyFile}", assemblyFile);
                }
            }

            _logger.LogInformation("Plugin discovery completed. Found {PluginCount} plugins", discoveredPlugins.Count);
            return discoveredPlugins;
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Error during plugin discovery in directory: {Directory}", directory);
            return discoveredPlugins;
        }
    }

    public async Task<PluginLoadResult> LoadPluginFromAssemblyAsync(string assemblyPath)
    {
        var stopwatch = System.Diagnostics.Stopwatch.StartNew();
        var result = new PluginLoadResult();

        try
        {
            _logger.LogDebug("Loading plugin assembly: {AssemblyPath}", assemblyPath);

            // Create isolated load context for the plugin
            var context = new PluginLoadContext(assemblyPath);
            var assembly = context.LoadFromAssemblyPath(assemblyPath);

            // Find plugin types
            var pluginTypes = assembly.GetTypes()
                .Where(t => typeof(IPlugin).IsAssignableFrom(t) && !t.IsInterface && !t.IsAbstract)
                .ToList();

            if (pluginTypes.Count == 0)
            {
                result.Success = false;
                result.Message = "No plugin implementations found in assembly";
                result.Errors.Add($"Assembly {Path.GetFileName(assemblyPath)} does not contain any IPlugin implementations");
                return result;
            }

            if (pluginTypes.Count > 1)
            {
                result.Warnings.Add($"Multiple plugin types found. Using first: {pluginTypes[0].Name}");
            }

            // Create plugin instance
            var pluginType = pluginTypes[0];
            var plugin = Activator.CreateInstance(pluginType) as IPlugin;

            if (plugin == null)
            {
                result.Success = false;
                result.Message = "Failed to create plugin instance";
                result.Errors.Add($"Could not create instance of type {pluginType.Name}");
                return result;
            }

            // Validate plugin
            await ValidatePluginAsync(plugin);

            // Check for existing plugin with same ID
            if (_loadedPlugins.ContainsKey(plugin.Id))
            {
                result.Success = false;
                result.Message = $"Plugin with ID '{plugin.Id}' is already loaded";
                result.Errors.Add($"Duplicate plugin ID: {plugin.Id}");
                return result;
            }

            // Initialize plugin
            var initSuccess = await plugin.InitializeAsync(null); // TODO: Pass service provider
            if (!initSuccess)
            {
                result.Success = false;
                result.Message = "Plugin initialization failed";
                result.Errors.Add($"Plugin {plugin.Name} failed to initialize");
                return result;
            }

            // Start plugin
            var startSuccess = await plugin.StartAsync();
            if (!startSuccess)
            {
                result.Success = false;
                result.Message = "Plugin start failed";
                result.Errors.Add($"Plugin {plugin.Name} failed to start");
                return result;
            }

            // Store plugin and context
            _loadedPlugins[plugin.Id] = plugin;
            _pluginContexts[plugin.Id] = context;
            _pluginInfos[plugin.Id] = new PluginInfo
            {
                Id = plugin.Id,
                Name = plugin.Name,
                Version = plugin.Version,
                Description = plugin.Description,
                AssemblyPath = assemblyPath,
                Metadata = plugin.Metadata,
                Status = PluginStatus.Running,
                LoadedAt = DateTime.UtcNow
            };

            result.Success = true;
            result.Message = $"Plugin '{plugin.Name}' loaded successfully";
            result.Plugin = plugin;
            result.LoadTime = stopwatch.Elapsed;

            OnPluginLoaded(new PluginLoadedEventArgs { Plugin = plugin, LoadTime = stopwatch.Elapsed });

            _logger.LogInformation("Plugin loaded successfully: {PluginName} v{Version} ({LoadTimeMs}ms)", 
                plugin.Name, plugin.Version, stopwatch.ElapsedMilliseconds);

            return result;
        }
        catch (Exception ex)
        {
            result.Success = false;
            result.Message = $"Error loading plugin: {ex.Message}";
            result.Errors.Add(ex.ToString());
            result.LoadTime = stopwatch.Elapsed;

            _logger.LogError(ex, "Error loading plugin from assembly: {AssemblyPath}", assemblyPath);
            OnPluginError(new PluginErrorEventArgs { PluginId = assemblyPath, Exception = ex, ErrorMessage = ex.Message });

            return result;
        }
    }

    public async Task ValidatePluginAsync(IPlugin plugin)
    {
        if (plugin == null)
            throw new ArgumentNullException(nameof(plugin));

        if (string.IsNullOrWhiteSpace(plugin.Id))
            throw new InvalidOperationException("Plugin ID cannot be empty");

        if (string.IsNullOrWhiteSpace(plugin.Name))
            throw new InvalidOperationException("Plugin Name cannot be empty");

        if (string.IsNullOrWhiteSpace(plugin.Version))
            throw new InvalidOperationException("Plugin Version cannot be empty");

        // Validate framework compatibility
        var currentFrameworkVersion = Environment.Version.ToString();
        if (!plugin.IsCompatible(currentFrameworkVersion))
        {
            throw new InvalidOperationException($"Plugin is not compatible with framework version {currentFrameworkVersion}");
        }

        _logger.LogDebug("Plugin validation successful: {PluginName}", plugin.Name);
    }

    private async Task<PluginInfo?> AnalyzeAssemblyAsync(string assemblyPath)
    {
        try
        {
            // Create a temporary load context for analysis
            var context = new PluginLoadContext(assemblyPath);
            var assembly = context.LoadFromAssemblyPath(assemblyPath);

            var pluginTypes = assembly.GetTypes()
                .Where(t => typeof(IPlugin).IsAssignableFrom(t) && !t.IsInterface && !t.IsAbstract)
                .ToList();

            if (pluginTypes.Count == 0)
                return null;

            // Create temporary instance for metadata extraction
            var pluginType = pluginTypes[0];
            var plugin = Activator.CreateInstance(pluginType) as IPlugin;
            
            if (plugin == null)
                return null;

            var pluginInfo = new PluginInfo
            {
                Id = plugin.Id,
                Name = plugin.Name,
                Version = plugin.Version,
                Description = plugin.Description,
                AssemblyPath = assemblyPath,
                Metadata = plugin.Metadata,
                Status = PluginStatus.Discovered
            };

            // Unload the temporary context
            context.Unload();

            return pluginInfo;
        }
        catch (Exception ex)
        {
            _logger.LogWarning(ex, "Failed to analyze assembly: {AssemblyPath}", assemblyPath);
            return null;
        }
    }

    private void OnPluginLoaded(PluginLoadedEventArgs args)
    {
        PluginLoaded?.Invoke(this, args);
    }

    private void OnPluginUnloaded(PluginUnloadedEventArgs args)
    {
        PluginUnloaded?.Invoke(this, args);
    }

    private void OnPluginError(PluginErrorEventArgs args)
    {
        PluginError?.Invoke(this, args);
    }
}

public class PluginLoadContext : AssemblyLoadContext
{
    private readonly AssemblyDependencyResolver _resolver;

    public PluginLoadContext(string pluginPath) : base(isCollectible: true)
    {
        _resolver = new AssemblyDependencyResolver(pluginPath);
    }

    protected override Assembly? Load(AssemblyName assemblyName)
    {
        var assemblyPath = _resolver.ResolveAssemblyToPath(assemblyName);
        if (assemblyPath != null)
        {
            return LoadFromAssemblyPath(assemblyPath);
        }

        return null;
    }

    protected override IntPtr LoadUnmanagedDll(string unmanagedDllName)
    {
        var libraryPath = _resolver.ResolveUnmanagedDllToPath(unmanagedDllName);
        if (libraryPath != null)
        {
            return LoadUnmanagedDllFromPath(libraryPath);
        }

        return IntPtr.Zero;
    }
}