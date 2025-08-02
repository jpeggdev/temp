using Microsoft.Extensions.Logging;
using Moq;
using Xunit;
using HeyDav.Infrastructure.Plugins;
using HeyDav.Application.TaskProcessing;

namespace HeyDav.Infrastructure.Tests.Plugins;

public class PluginManagerTests
{
    private readonly Mock<ILogger<PluginManager>> _mockLogger;
    private readonly PluginManager _pluginManager;

    public PluginManagerTests()
    {
        _mockLogger = new Mock<ILogger<PluginManager>>();
        _pluginManager = new PluginManager(_mockLogger.Object);
    }

    [Fact]
    public async Task GetLoadedPluginsAsync_InitialState_ReturnsEmptyList()
    {
        // Act
        var plugins = await _pluginManager.GetLoadedPluginsAsync();

        // Assert
        Assert.Empty(plugins);
    }

    [Fact]
    public async Task GetPluginByIdAsync_NonExistentPlugin_ReturnsNull()
    {
        // Act
        var plugin = await _pluginManager.GetPluginByIdAsync("non-existent");

        // Assert
        Assert.Null(plugin);
    }

    [Fact]
    public async Task LoadPluginAsync_NonExistentFile_ReturnsFalse()
    {
        // Act
        var result = await _pluginManager.LoadPluginAsync("non-existent.dll");

        // Assert
        Assert.False(result);
    }

    [Fact]
    public async Task UnloadPluginAsync_NonExistentPlugin_ReturnsFalse()
    {
        // Act
        var result = await _pluginManager.UnloadPluginAsync("non-existent");

        // Assert
        Assert.False(result);
    }

    [Fact]
    public async Task ReloadPluginAsync_NonExistentPlugin_ReturnsFalse()
    {
        // Act
        var result = await _pluginManager.ReloadPluginAsync("non-existent");

        // Assert
        Assert.False(result);
    }

    [Fact]
    public async Task DiscoverPluginsAsync_NonExistentDirectory_ReturnsEmptyList()
    {
        // Act
        var plugins = await _pluginManager.DiscoverPluginsAsync("non-existent-directory");

        // Assert
        Assert.Empty(plugins);
    }

    [Fact]
    public void ValidatePluginAsync_NullPlugin_ThrowsArgumentNullException()
    {
        // Act & Assert
        Assert.ThrowsAsync<ArgumentNullException>(() => _pluginManager.ValidatePluginAsync(null!));
    }

    [Fact]
    public async Task ValidatePluginAsync_PluginWithEmptyId_ThrowsInvalidOperationException()
    {
        // Arrange
        var mockPlugin = new Mock<IPlugin>();
        mockPlugin.Setup(x => x.Id).Returns("");
        mockPlugin.Setup(x => x.Name).Returns("Test Plugin");
        mockPlugin.Setup(x => x.Version).Returns("1.0.0");

        // Act & Assert
        await Assert.ThrowsAsync<InvalidOperationException>(() => _pluginManager.ValidatePluginAsync(mockPlugin.Object));
    }

    [Fact]
    public async Task ValidatePluginAsync_PluginWithEmptyName_ThrowsInvalidOperationException()
    {
        // Arrange
        var mockPlugin = new Mock<IPlugin>();
        mockPlugin.Setup(x => x.Id).Returns("test-plugin");
        mockPlugin.Setup(x => x.Name).Returns("");
        mockPlugin.Setup(x => x.Version).Returns("1.0.0");

        // Act & Assert
        await Assert.ThrowsAsync<InvalidOperationException>(() => _pluginManager.ValidatePluginAsync(mockPlugin.Object));
    }

    [Fact]
    public async Task ValidatePluginAsync_PluginWithEmptyVersion_ThrowsInvalidOperationException()
    {
        // Arrange
        var mockPlugin = new Mock<IPlugin>();
        mockPlugin.Setup(x => x.Id).Returns("test-plugin");
        mockPlugin.Setup(x => x.Name).Returns("Test Plugin");
        mockPlugin.Setup(x => x.Version).Returns("");

        // Act & Assert
        await Assert.ThrowsAsync<InvalidOperationException>(() => _pluginManager.ValidatePluginAsync(mockPlugin.Object));
    }

    [Fact]
    public async Task ValidatePluginAsync_IncompatiblePlugin_ThrowsInvalidOperationException()
    {
        // Arrange
        var mockPlugin = new Mock<IPlugin>();
        mockPlugin.Setup(x => x.Id).Returns("test-plugin");
        mockPlugin.Setup(x => x.Name).Returns("Test Plugin");
        mockPlugin.Setup(x => x.Version).Returns("1.0.0");
        mockPlugin.Setup(x => x.IsCompatible(It.IsAny<string>())).Returns(false);

        // Act & Assert
        await Assert.ThrowsAsync<InvalidOperationException>(() => _pluginManager.ValidatePluginAsync(mockPlugin.Object));
    }

    [Fact]
    public async Task ValidatePluginAsync_ValidPlugin_DoesNotThrow()
    {
        // Arrange
        var mockPlugin = new Mock<IPlugin>();
        mockPlugin.Setup(x => x.Id).Returns("test-plugin");
        mockPlugin.Setup(x => x.Name).Returns("Test Plugin");
        mockPlugin.Setup(x => x.Version).Returns("1.0.0");
        mockPlugin.Setup(x => x.IsCompatible(It.IsAny<string>())).Returns(true);

        // Act & Assert
        await _pluginManager.ValidatePluginAsync(mockPlugin.Object);
        // Should not throw
    }

    [Fact]
    public async Task LoadPluginFromAssemblyAsync_NonExistentAssembly_ReturnsFailure()
    {
        // Act
        var result = await _pluginManager.LoadPluginFromAssemblyAsync("non-existent.dll");

        // Assert
        Assert.False(result.Success);
        Assert.Contains("error", result.Message.ToLowerInvariant());
        Assert.NotEmpty(result.Errors);
    }

    [Fact]
    public void PluginEvents_CanSubscribe()
    {
        // Arrange
        var pluginLoadedFired = false;
        var pluginUnloadedFired = false;
        var pluginErrorFired = false;

        _pluginManager.PluginLoaded += (sender, args) => pluginLoadedFired = true;
        _pluginManager.PluginUnloaded += (sender, args) => pluginUnloadedFired = true;
        _pluginManager.PluginError += (sender, args) => pluginErrorFired = true;

        // Act - Events are internal and would be triggered by actual plugin operations
        // For this test, we just verify they can be subscribed to without errors

        // Assert
        Assert.False(pluginLoadedFired); // Events not fired yet
        Assert.False(pluginUnloadedFired);
        Assert.False(pluginErrorFired);
    }
}

// Mock plugin for testing
public class TestPlugin : IPlugin
{
    public string Id => "test-plugin";
    public string Name => "Test Plugin";
    public string Version => "1.0.0";
    public string Description => "A test plugin";
    public PluginMetadata Metadata => new();

    public Task<bool> InitializeAsync(IServiceProvider serviceProvider)
    {
        return Task.FromResult(true);
    }

    public Task<bool> StartAsync()
    {
        return Task.FromResult(true);
    }

    public Task<bool> StopAsync()
    {
        return Task.FromResult(true);
    }

    public Task<bool> UnloadAsync()
    {
        return Task.FromResult(true);
    }

    public Task<PluginCapabilities> GetCapabilitiesAsync()
    {
        return Task.FromResult(new PluginCapabilities
        {
            SupportedCommands = new List<string> { "test" }
        });
    }

    public Task<object?> ExecuteAsync(string command, Dictionary<string, object>? parameters = null)
    {
        return Task.FromResult<object?>("test result");
    }

    public bool IsCompatible(string frameworkVersion)
    {
        return true;
    }
}