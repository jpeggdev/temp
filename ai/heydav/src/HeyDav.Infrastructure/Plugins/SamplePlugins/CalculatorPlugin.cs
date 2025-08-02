using HeyDav.Application.TaskProcessing;
using System.Text.RegularExpressions;

namespace HeyDav.Infrastructure.Plugins.SamplePlugins;

public class CalculatorPlugin : BasePlugin
{
    public override string Id => "calculator-plugin";
    public override string Name => "Calculator Plugin";
    public override string Version => "1.0.0";
    public override string Description => "A mathematical calculator plugin that can perform basic arithmetic operations";

    public override PluginMetadata Metadata => new()
    {
        Author = "HeyDav Team",
        License = "MIT",
        Tags = new List<string> { "calculator", "math", "arithmetic", "utility" },
        MinFrameworkVersion = "8.0.0",
        RequiredPermissions = new List<string>(),
        Sandbox = new PluginSandboxSettings
        {
            EnableSandbox = true,
            AllowFileAccess = false,
            AllowNetworkAccess = false,
            ResourceLimits = new Dictionary<string, object>
            {
                { "maxMemoryMB", 10 },
                { "maxExecutionTimeMs", 5000 }
            }
        }
    };

    public override async Task<PluginCapabilities> GetCapabilitiesAsync()
    {
        return new PluginCapabilities
        {
            SupportedCommands = new List<string> 
            { 
                "calculate", "calc", "add", "subtract", "multiply", "divide", 
                "power", "sqrt", "abs", "factorial", "evaluate"
            },
            ProvidedServices = new List<string> { "CalculatorService", "MathService" },
            ConsumedServices = new List<string>(),
            SupportsHotReload = true,
            SupportsConfiguration = true,
            ExtensionPoints = new Dictionary<string, object>
            {
                { "precision", 10 },
                { "maxNumberValue", double.MaxValue },
                { "supportedOperations", new[] { "+", "-", "*", "/", "^", "sqrt", "abs", "!" } }
            }
        };
    }

    protected override async Task<bool> OnInitializeAsync()
    {
        LogInfo("Calculator plugin initializing...");
        
        // Initialize any mathematical constants or settings
        await Task.Delay(5);
        
        LogInfo("Calculator plugin initialized successfully");
        return true;
    }

    protected override async Task<bool> OnStartAsync()
    {
        LogInfo("Calculator plugin starting...");
        
        // Start calculator service
        await Task.Delay(5);
        
        LogInfo("Calculator plugin started successfully");
        return true;
    }

    protected override async Task<object?> OnExecuteAsync(string command, Dictionary<string, object> parameters)
    {
        LogDebug("Executing calculator command: {Command}", command);

        try
        {
            switch (command.ToLowerInvariant())
            {
                case "calculate":
                case "calc":
                case "evaluate":
                    return await HandleCalculateCommand(parameters);
                
                case "add":
                    return await HandleArithmeticCommand(parameters, "+");
                
                case "subtract":
                    return await HandleArithmeticCommand(parameters, "-");
                
                case "multiply":
                    return await HandleArithmeticCommand(parameters, "*");
                
                case "divide":
                    return await HandleArithmeticCommand(parameters, "/");
                
                case "power":
                    return await HandleArithmeticCommand(parameters, "^");
                
                case "sqrt":
                    return await HandleSqrtCommand(parameters);
                
                case "abs":
                    return await HandleAbsCommand(parameters);
                
                case "factorial":
                    return await HandleFactorialCommand(parameters);
                
                default:
                    throw new ArgumentException($"Unknown calculator command: {command}");
            }
        }
        catch (Exception ex)
        {
            LogError(ex, "Error executing calculator command: {Command}", command);
            
            return new
            {
                success = false,
                error = ex.Message,
                command = command,
                timestamp = DateTime.UtcNow
            };
        }
    }

    private async Task<object> HandleCalculateCommand(Dictionary<string, object> parameters)
    {
        var expression = parameters.GetValueOrDefault("expression", "")?.ToString() ?? "";
        
        if (string.IsNullOrWhiteSpace(expression))
        {
            throw new ArgumentException("Expression parameter is required");
        }

        LogDebug("Evaluating expression: {Expression}", expression);

        // Simple expression evaluator (in production, you'd use a proper parser)
        var result = await EvaluateExpressionAsync(expression);
        
        return new
        {
            success = true,
            expression = expression,
            result = result,
            timestamp = DateTime.UtcNow,
            plugin_version = Version
        };
    }

    private async Task<object> HandleArithmeticCommand(Dictionary<string, object> parameters, string operation)
    {
        var operand1 = GetDoubleParameter(parameters, "operand1", "a", "x", "first");
        var operand2 = GetDoubleParameter(parameters, "operand2", "b", "y", "second");

        if (!operand1.HasValue || !operand2.HasValue)
        {
            throw new ArgumentException("Two numeric operands are required");
        }

        var result = operation switch
        {
            "+" => operand1.Value + operand2.Value,
            "-" => operand1.Value - operand2.Value,
            "*" => operand1.Value * operand2.Value,
            "/" => operand2.Value == 0 ? throw new DivideByZeroException("Cannot divide by zero") : operand1.Value / operand2.Value,
            "^" => Math.Pow(operand1.Value, operand2.Value),
            _ => throw new ArgumentException($"Unknown operation: {operation}")
        };

        await Task.CompletedTask; // Simulate async work

        return new
        {
            success = true,
            operation = operation,
            operand1 = operand1.Value,
            operand2 = operand2.Value,
            result = result,
            timestamp = DateTime.UtcNow
        };
    }

    private async Task<object> HandleSqrtCommand(Dictionary<string, object> parameters)
    {
        var value = GetDoubleParameter(parameters, "value", "number", "x");
        
        if (!value.HasValue)
        {
            throw new ArgumentException("Numeric value parameter is required");
        }

        if (value.Value < 0)
        {
            throw new ArgumentException("Cannot calculate square root of negative number");
        }

        var result = Math.Sqrt(value.Value);
        
        await Task.CompletedTask;

        return new
        {
            success = true,
            operation = "sqrt",
            input = value.Value,
            result = result,
            timestamp = DateTime.UtcNow
        };
    }

    private async Task<object> HandleAbsCommand(Dictionary<string, object> parameters)
    {
        var value = GetDoubleParameter(parameters, "value", "number", "x");
        
        if (!value.HasValue)
        {
            throw new ArgumentException("Numeric value parameter is required");
        }

        var result = Math.Abs(value.Value);
        
        await Task.CompletedTask;

        return new
        {
            success = true,
            operation = "abs",
            input = value.Value,
            result = result,
            timestamp = DateTime.UtcNow
        };
    }

    private async Task<object> HandleFactorialCommand(Dictionary<string, object> parameters)
    {
        var value = GetDoubleParameter(parameters, "value", "number", "n");
        
        if (!value.HasValue)
        {
            throw new ArgumentException("Numeric value parameter is required");
        }

        var intValue = (int)value.Value;
        if (intValue != value.Value || intValue < 0)
        {
            throw new ArgumentException("Factorial requires a non-negative integer");
        }

        if (intValue > 20) // Prevent very large calculations
        {
            throw new ArgumentException("Factorial input too large (max 20)");
        }

        var result = await CalculateFactorialAsync(intValue);

        return new
        {
            success = true,
            operation = "factorial",
            input = intValue,
            result = result,
            timestamp = DateTime.UtcNow
        };
    }

    private async Task<double> EvaluateExpressionAsync(string expression)
    {
        // Simple expression evaluator - in production you'd use a proper math parser
        expression = expression.Replace(" ", "");
        
        // Handle basic operations with regex
        var addPattern = @"^(-?\d+(?:\.\d+)?)\+(-?\d+(?:\.\d+)?)$";
        var subtractPattern = @"^(-?\d+(?:\.\d+)?)-(-?\d+(?:\.\d+)?)$";
        var multiplyPattern = @"^(-?\d+(?:\.\d+)?)\*(-?\d+(?:\.\d+)?)$";
        var dividePattern = @"^(-?\d+(?:\.\d+)?)/(-?\d+(?:\.\d+)?)$";

        Match match;
        
        if ((match = Regex.Match(expression, addPattern)).Success)
        {
            return double.Parse(match.Groups[1].Value) + double.Parse(match.Groups[2].Value);
        }
        else if ((match = Regex.Match(expression, subtractPattern)).Success)
        {
            return double.Parse(match.Groups[1].Value) - double.Parse(match.Groups[2].Value);
        }
        else if ((match = Regex.Match(expression, multiplyPattern)).Success)
        {
            return double.Parse(match.Groups[1].Value) * double.Parse(match.Groups[2].Value);
        }
        else if ((match = Regex.Match(expression, dividePattern)).Success)
        {
            var divisor = double.Parse(match.Groups[2].Value);
            if (divisor == 0) throw new DivideByZeroException("Division by zero");
            return double.Parse(match.Groups[1].Value) / divisor;
        }
        else if (double.TryParse(expression, out var number))
        {
            return number;
        }
        else
        {
            throw new ArgumentException($"Cannot evaluate expression: {expression}");
        }
    }

    private async Task<long> CalculateFactorialAsync(int n)
    {
        if (n <= 1) return 1;
        
        long result = 1;
        for (int i = 2; i <= n; i++)
        {
            result *= i;
            
            // Yield control occasionally for large calculations
            if (i % 5 == 0)
            {
                await Task.Yield();
            }
        }
        
        return result;
    }

    private double? GetDoubleParameter(Dictionary<string, object> parameters, params string[] keys)
    {
        foreach (var key in keys)
        {
            if (parameters.TryGetValue(key, out var value))
            {
                if (double.TryParse(value?.ToString(), out var doubleValue))
                {
                    return doubleValue;
                }
            }
        }
        
        return null;
    }
}