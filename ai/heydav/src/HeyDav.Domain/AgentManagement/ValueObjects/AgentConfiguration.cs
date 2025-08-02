using HeyDav.Domain.Common.Base;

namespace HeyDav.Domain.AgentManagement.ValueObjects;

public class AgentConfiguration : ValueObject
{
    public string ModelName { get; }
    public int MaxTokens { get; }
    public double Temperature { get; }
    public int MaxConcurrentTasks { get; }
    public TimeSpan TaskTimeout { get; }
    public Dictionary<string, string> CustomSettings { get; }

    private AgentConfiguration(
        string modelName,
        int maxTokens,
        double temperature,
        int maxConcurrentTasks,
        TimeSpan taskTimeout,
        Dictionary<string, string> customSettings)
    {
        ModelName = modelName;
        MaxTokens = maxTokens;
        Temperature = temperature;
        MaxConcurrentTasks = maxConcurrentTasks;
        TaskTimeout = taskTimeout;
        CustomSettings = customSettings;
    }

    public static AgentConfiguration Create(
        string modelName,
        int maxTokens = 4000,
        double temperature = 0.7,
        int maxConcurrentTasks = 3,
        TimeSpan? taskTimeout = null,
        Dictionary<string, string>? customSettings = null)
    {
        if (string.IsNullOrWhiteSpace(modelName))
            throw new ArgumentException("Model name cannot be empty", nameof(modelName));

        if (maxTokens <= 0)
            throw new ArgumentException("Max tokens must be positive", nameof(maxTokens));

        if (temperature < 0 || temperature > 2)
            throw new ArgumentException("Temperature must be between 0 and 2", nameof(temperature));

        if (maxConcurrentTasks <= 0)
            throw new ArgumentException("Max concurrent tasks must be positive", nameof(maxConcurrentTasks));

        return new AgentConfiguration(
            modelName,
            maxTokens,
            temperature,
            maxConcurrentTasks,
            taskTimeout ?? TimeSpan.FromMinutes(30),
            customSettings ?? new Dictionary<string, string>());
    }

    protected override IEnumerable<object> GetEqualityComponents()
    {
        yield return ModelName;
        yield return MaxTokens;
        yield return Temperature;
        yield return MaxConcurrentTasks;
        yield return TaskTimeout;
        
        foreach (var setting in CustomSettings.OrderBy(x => x.Key))
        {
            yield return setting.Key;
            yield return setting.Value;
        }
    }
}