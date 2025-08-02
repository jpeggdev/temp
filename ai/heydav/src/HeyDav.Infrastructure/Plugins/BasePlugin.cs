using Microsoft.Extensions.Logging;
using HeyDav.Application.TaskProcessing;

namespace HeyDav.Infrastructure.Plugins;

public abstract class BasePlugin : IPlugin
{
    protected IServiceProvider? ServiceProvider;
    protected ILogger? Logger;

    public abstract string Id { get; }
    public abstract string Name { get; }
    public abstract string Version { get; }
    public abstract string Description { get; }
    public virtual PluginMetadata Metadata { get; } = new();

    protected PluginStatus Status { get; set; } = PluginStatus.Discovered;

    public virtual async Task<bool> InitializeAsync(IServiceProvider serviceProvider)
    {
        try
        {
            ServiceProvider = serviceProvider;
            Logger = serviceProvider?.GetService(typeof(ILogger<>).MakeGenericType(GetType())) as ILogger;
            
            Logger?.LogInformation("Initializing plugin: {PluginName} v{Version}", Name, Version);
            
            Status = PluginStatus.Loading;
            
            var success = await OnInitializeAsync();
            
            if (success)
            {
                Status = PluginStatus.Loaded;
                Logger?.LogInformation("Plugin initialized successfully: {PluginName}", Name);
            }
            else
            {
                Status = PluginStatus.Failed;
                Logger?.LogError("Plugin initialization failed: {PluginName}", Name);
            }
            
            return success;
        }
        catch (Exception ex)
        {
            Status = PluginStatus.Failed;
            Logger?.LogError(ex, "Error initializing plugin: {PluginName}", Name);
            return false;
        }
    }

    public virtual async Task<bool> StartAsync()
    {
        try
        {
            if (Status != PluginStatus.Loaded)
            {
                Logger?.LogError("Cannot start plugin in current status: {Status}", Status);
                return false;
            }

            Logger?.LogInformation("Starting plugin: {PluginName}", Name);
            
            var success = await OnStartAsync();
            
            if (success)
            {
                Status = PluginStatus.Running;
                Logger?.LogInformation("Plugin started successfully: {PluginName}", Name);
            }
            else
            {
                Status = PluginStatus.Failed;
                Logger?.LogError("Plugin start failed: {PluginName}", Name);
            }
            
            return success;
        }
        catch (Exception ex)
        {
            Status = PluginStatus.Failed;
            Logger?.LogError(ex, "Error starting plugin: {PluginName}", Name);
            return false;
        }
    }

    public virtual async Task<bool> StopAsync()
    {
        try
        {
            if (Status != PluginStatus.Running)
            {
                Logger?.LogWarning("Plugin is not running: {PluginName}", Name);
                return true;
            }

            Logger?.LogInformation("Stopping plugin: {PluginName}", Name);
            
            var success = await OnStopAsync();
            
            if (success)
            {
                Status = PluginStatus.Stopped;
                Logger?.LogInformation("Plugin stopped successfully: {PluginName}", Name);
            }
            else
            {
                Logger?.LogError("Plugin stop failed: {PluginName}", Name);
            }
            
            return success;
        }
        catch (Exception ex)
        {
            Logger?.LogError(ex, "Error stopping plugin: {PluginName}", Name);
            return false;
        }
    }

    public virtual async Task<bool> UnloadAsync()
    {
        try
        {
            Logger?.LogInformation("Unloading plugin: {PluginName}", Name);
            
            if (Status == PluginStatus.Running)
            {
                await StopAsync();
            }
            
            Status = PluginStatus.Unloading;
            
            var success = await OnUnloadAsync();
            
            if (success)
            {
                Status = PluginStatus.Unloaded;
                Logger?.LogInformation("Plugin unloaded successfully: {PluginName}", Name);
            }
            else
            {
                Logger?.LogError("Plugin unload failed: {PluginName}", Name);
            }
            
            return success;
        }
        catch (Exception ex)
        {
            Logger?.LogError(ex, "Error unloading plugin: {PluginName}", Name);
            return false;
        }
    }

    public abstract Task<PluginCapabilities> GetCapabilitiesAsync();

    public virtual async Task<object?> ExecuteAsync(string command, Dictionary<string, object>? parameters = null)
    {
        try
        {
            if (Status != PluginStatus.Running)
            {
                throw new InvalidOperationException($"Plugin is not running. Current status: {Status}");
            }

            Logger?.LogDebug("Executing command in plugin: {PluginName}, Command: {Command}", Name, command);
            
            var result = await OnExecuteAsync(command, parameters ?? new Dictionary<string, object>());
            
            Logger?.LogDebug("Command executed successfully in plugin: {PluginName}", Name);
            
            return result;
        }
        catch (Exception ex)
        {
            Logger?.LogError(ex, "Error executing command in plugin: {PluginName}, Command: {Command}", Name, command);
            throw;
        }
    }

    public virtual bool IsCompatible(string frameworkVersion)
    {
        try
        {
            if (string.IsNullOrEmpty(Metadata.MinFrameworkVersion) && string.IsNullOrEmpty(Metadata.MaxFrameworkVersion))
            {
                return true; // No version constraints
            }

            var currentVersion = System.Version.Parse(frameworkVersion);
            
            if (!string.IsNullOrEmpty(Metadata.MinFrameworkVersion))
            {
                var minVersion = System.Version.Parse(Metadata.MinFrameworkVersion);
                if (currentVersion < minVersion)
                {
                    return false;
                }
            }
            
            if (!string.IsNullOrEmpty(Metadata.MaxFrameworkVersion))
            {
                var maxVersion = System.Version.Parse(Metadata.MaxFrameworkVersion);
                if (currentVersion > maxVersion)
                {
                    return false;
                }
            }
            
            return true;
        }
        catch (Exception ex)
        {
            Logger?.LogError(ex, "Error checking framework compatibility for plugin: {PluginName}", Name);
            return false;
        }
    }

    // Protected virtual methods for derived classes to override
    protected virtual Task<bool> OnInitializeAsync()
    {
        return Task.FromResult(true);
    }

    protected virtual Task<bool> OnStartAsync()
    {
        return Task.FromResult(true);
    }

    protected virtual Task<bool> OnStopAsync()
    {
        return Task.FromResult(true);
    }

    protected virtual Task<bool> OnUnloadAsync()
    {
        return Task.FromResult(true);
    }

    protected abstract Task<object?> OnExecuteAsync(string command, Dictionary<string, object> parameters);

    // Helper methods for derived classes
    protected T? GetService<T>() where T : class
    {
        return ServiceProvider?.GetService(typeof(T)) as T;
    }

    protected void LogInfo(string message, params object[] args)
    {
        Logger?.LogInformation(message, args);
    }

    protected void LogWarning(string message, params object[] args)
    {
        Logger?.LogWarning(message, args);
    }

    protected void LogError(Exception? exception, string message, params object[] args)
    {
        Logger?.LogError(exception, message, args);
    }

    protected void LogDebug(string message, params object[] args)
    {
        Logger?.LogDebug(message, args);
    }
}