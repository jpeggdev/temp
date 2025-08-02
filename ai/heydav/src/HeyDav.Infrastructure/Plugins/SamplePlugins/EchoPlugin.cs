using HeyDav.Application.TaskProcessing;

namespace HeyDav.Infrastructure.Plugins.SamplePlugins;

public class EchoPlugin : BasePlugin
{
    public override string Id => "echo-plugin";
    public override string Name => "Echo Plugin";
    public override string Version => "1.0.0";
    public override string Description => "A sample plugin that echoes back input with additional information";

    public override PluginMetadata Metadata => new()
    {
        Author = "HeyDav Team",
        License = "MIT",
        Tags = new List<string> { "sample", "echo", "demo" },
        MinFrameworkVersion = "8.0.0",
        RequiredPermissions = new List<string>(),
        Sandbox = new PluginSandboxSettings
        {
            EnableSandbox = false,
            AllowFileAccess = false,
            AllowNetworkAccess = false
        }
    };

    public override async Task<PluginCapabilities> GetCapabilitiesAsync()
    {
        return new PluginCapabilities
        {
            SupportedCommands = new List<string> { "echo", "ping", "test" },
            ProvidedServices = new List<string> { "EchoService" },
            ConsumedServices = new List<string>(),
            SupportsHotReload = true,
            SupportsConfiguration = true,
            ExtensionPoints = new Dictionary<string, object>
            {
                { "commandPrefix", "echo:" },
                { "maxMessageLength", 1000 }
            }
        };
    }

    protected override async Task<bool> OnInitializeAsync()
    {
        LogInfo("Echo plugin initializing...");
        
        // Perform any initialization logic here
        await Task.Delay(10); // Simulate initialization work
        
        LogInfo("Echo plugin initialized successfully");
        return true;
    }

    protected override async Task<bool> OnStartAsync()
    {
        LogInfo("Echo plugin starting...");
        
        // Perform any startup logic here
        await Task.Delay(5); // Simulate startup work
        
        LogInfo("Echo plugin started successfully");
        return true;
    }

    protected override async Task<bool> OnStopAsync()
    {
        LogInfo("Echo plugin stopping...");
        
        // Perform any cleanup logic here
        await Task.Delay(5); // Simulate cleanup work
        
        LogInfo("Echo plugin stopped successfully");
        return true;
    }

    protected override async Task<object?> OnExecuteAsync(string command, Dictionary<string, object> parameters)
    {
        LogDebug("Executing command: {Command} with {ParameterCount} parameters", command, parameters.Count);

        switch (command.ToLowerInvariant())
        {
            case "echo":
                return await HandleEchoCommand(parameters);
            
            case "ping":
                return await HandlePingCommand(parameters);
            
            case "test":
                return await HandleTestCommand(parameters);
            
            default:
                throw new ArgumentException($"Unknown command: {command}");
        }
    }

    private async Task<object> HandleEchoCommand(Dictionary<string, object> parameters)
    {
        var message = parameters.GetValueOrDefault("message", "Hello from Echo Plugin!")?.ToString() ?? "";
        var timestamp = DateTime.UtcNow;
        var pluginInfo = $"{Name} v{Version}";

        LogDebug("Processing echo command with message: {Message}", message);

        // Simulate some processing time
        await Task.Delay(10);

        return new
        {
            original_message = message,
            echo_response = $"Echo: {message}",
            plugin_info = pluginInfo,
            timestamp = timestamp,
            parameters_received = parameters.Count,
            execution_id = Guid.NewGuid().ToString("N")[..8]
        };
    }

    private async Task<object> HandlePingCommand(Dictionary<string, object> parameters)
    {
        var timestamp = DateTime.UtcNow;
        
        LogDebug("Processing ping command");

        await Task.Delay(5); // Simulate network latency

        return new
        {
            response = "pong",
            plugin_name = Name,
            plugin_version = Version,
            timestamp = timestamp,
            latency_ms = 5
        };
    }

    private async Task<object> HandleTestCommand(Dictionary<string, object> parameters)
    {
        LogDebug("Processing test command");

        var testResults = new List<object>();

        // Test 1: Basic functionality
        testResults.Add(new
        {
            test_name = "basic_functionality",
            passed = true,
            message = "Plugin basic functionality is working"
        });

        // Test 2: Parameter handling
        var hasParameters = parameters.Count > 0;
        testResults.Add(new
        {
            test_name = "parameter_handling",
            passed = true,
            message = $"Received {parameters.Count} parameters",
            parameters = parameters.Keys.ToList()
        });

        // Test 3: Async operations
        await Task.Delay(20); // Simulate async work
        testResults.Add(new
        {
            test_name = "async_operations",
            passed = true,
            message = "Async operations are working correctly"
        });

        var allPassed = testResults.All(r => (bool)((dynamic)r).passed);

        return new
        {
            plugin_name = Name,
            plugin_version = Version,
            test_results = testResults,
            overall_result = allPassed ? "PASS" : "FAIL",
            execution_time = DateTime.UtcNow,
            total_tests = testResults.Count,
            passed_tests = testResults.Count(r => (bool)((dynamic)r).passed)
        };
    }
}