using HeyDav.Domain.Common.Base;
using HeyDav.Domain.AgentManagement.Enums;
using HeyDav.Domain.AgentManagement.ValueObjects;
using HeyDav.Domain.AgentManagement.Events;

namespace HeyDav.Domain.AgentManagement.Entities;

public class AIAgent : AggregateRoot
{
    private readonly List<string> _capabilities = new();
    private readonly List<AgentTask> _currentTasks = new();
    private readonly List<AgentSpecialization> _specializations = new();

    public string Name { get; private set; }
    public string? Description { get; private set; }
    public AgentType Type { get; private set; }
    public AgentStatus Status { get; private set; }
    public AgentConfiguration Configuration { get; private set; }
    public DateTime? LastActiveAt { get; private set; }
    public DateTime? LastHealthCheckAt { get; private set; }
    public string? LastError { get; private set; }
    public int SuccessfulTasksCount { get; private set; }
    public int FailedTasksCount { get; private set; }
    public double AverageResponseTime { get; private set; }
    public IReadOnlyList<string> Capabilities => _capabilities.AsReadOnly();
    public IReadOnlyList<AgentTask> CurrentTasks => _currentTasks.AsReadOnly();
    public IReadOnlyList<AgentSpecialization> Specializations => _specializations.AsReadOnly();

    private AIAgent()
    {
        // EF Constructor
        Name = string.Empty;
        Configuration = AgentConfiguration.Create("default");
        Status = AgentStatus.Inactive;
        SuccessfulTasksCount = 0;
        FailedTasksCount = 0;
        AverageResponseTime = 0.0;
    }

    private AIAgent(
        string name,
        AgentType type,
        AgentConfiguration configuration,
        string? description = null)
    {
        Name = name;
        Type = type;
        Configuration = configuration;
        Description = description;
        Status = AgentStatus.Inactive;
        SuccessfulTasksCount = 0;
        FailedTasksCount = 0;
        AverageResponseTime = 0.0;
    }

    public static AIAgent Create(
        string name,
        AgentType type,
        AgentConfiguration configuration,
        string? description = null)
    {
        if (string.IsNullOrWhiteSpace(name))
            throw new ArgumentException("Agent name cannot be empty", nameof(name));

        if (configuration == null)
            throw new ArgumentNullException(nameof(configuration));

        var agent = new AIAgent(name, type, configuration, description);
        agent.AddDomainEvent(new AgentCreatedEvent(agent.Id, name, type));
        return agent;
    }

    public void UpdateName(string name)
    {
        if (string.IsNullOrWhiteSpace(name))
            throw new ArgumentException("Agent name cannot be empty", nameof(name));

        Name = name;
        UpdateTimestamp();
    }

    public void UpdateDescription(string? description)
    {
        Description = description;
        UpdateTimestamp();
    }

    public void UpdateConfiguration(AgentConfiguration configuration)
    {
        Configuration = configuration ?? throw new ArgumentNullException(nameof(configuration));
        UpdateTimestamp();
        AddDomainEvent(new AgentConfigurationUpdatedEvent(Id, configuration));
    }

    public void Activate()
    {
        if (Status == AgentStatus.Active)
            return;

        Status = AgentStatus.Active;
        LastActiveAt = DateTime.UtcNow;
        LastError = null;
        UpdateTimestamp();
        AddDomainEvent(new AgentActivatedEvent(Id));
    }

    public void Deactivate()
    {
        if (Status == AgentStatus.Inactive)
            return;

        Status = AgentStatus.Inactive;
        UpdateTimestamp();
        AddDomainEvent(new AgentDeactivatedEvent(Id));
    }

    public void SetError(string error)
    {
        if (string.IsNullOrWhiteSpace(error))
            throw new ArgumentException("Error message cannot be empty", nameof(error));

        Status = AgentStatus.Error;
        LastError = error;
        UpdateTimestamp();
        AddDomainEvent(new AgentErrorEvent(Id, error));
    }

    public void SetMaintenance()
    {
        Status = AgentStatus.Maintenance;
        UpdateTimestamp();
    }

    public void RecordHealthCheck()
    {
        LastHealthCheckAt = DateTime.UtcNow;
        
        if (Status == AgentStatus.Error)
        {
            Status = AgentStatus.Active;
            LastError = null;
        }
        
        UpdateTimestamp();
    }

    public void AddCapability(string capability)
    {
        if (string.IsNullOrWhiteSpace(capability))
            throw new ArgumentException("Capability cannot be empty", nameof(capability));

        if (!_capabilities.Contains(capability))
        {
            _capabilities.Add(capability);
            UpdateTimestamp();
        }
    }

    public void RemoveCapability(string capability)
    {
        if (_capabilities.Remove(capability))
        {
            UpdateTimestamp();
        }
    }

    public bool CanAcceptTask()
    {
        return Status == AgentStatus.Active && 
               _currentTasks.Count < Configuration.MaxConcurrentTasks;
    }

    public void AssignTask(AgentTask task)
    {
        if (task == null)
            throw new ArgumentNullException(nameof(task));

        if (!CanAcceptTask())
            throw new InvalidOperationException("Agent cannot accept more tasks");

        task.AssignToAgent(Id);
        _currentTasks.Add(task);
        
        if (_currentTasks.Count >= Configuration.MaxConcurrentTasks)
        {
            Status = AgentStatus.Busy;
        }

        UpdateTimestamp();
        AddDomainEvent(new TaskAssignedToAgentEvent(Id, task.Id));
    }

    public void CompleteTask(Guid taskId, TimeSpan responseTime)
    {
        var task = _currentTasks.FirstOrDefault(t => t.Id == taskId);
        if (task == null)
            throw new InvalidOperationException("Task not found in current tasks");

        task.Complete();
        _currentTasks.Remove(task);
        
        SuccessfulTasksCount++;
        UpdateAverageResponseTime(responseTime);
        
        if (Status == AgentStatus.Busy)
        {
            Status = AgentStatus.Active;
        }

        LastActiveAt = DateTime.UtcNow;
        UpdateTimestamp();
        AddDomainEvent(new TaskCompletedByAgentEvent(Id, taskId, responseTime));
    }

    public void FailTask(Guid taskId, string error)
    {
        var task = _currentTasks.FirstOrDefault(t => t.Id == taskId);
        if (task == null)
            throw new InvalidOperationException("Task not found in current tasks");

        task.Fail(error);
        _currentTasks.Remove(task);
        
        FailedTasksCount++;
        
        if (Status == AgentStatus.Busy)
        {
            Status = AgentStatus.Active;
        }

        UpdateTimestamp();
        AddDomainEvent(new TaskFailedByAgentEvent(Id, taskId, error));
    }

    public bool HasCapability(string capability)
    {
        return _capabilities.Contains(capability);
    }

    public double GetSuccessRate()
    {
        var totalTasks = SuccessfulTasksCount + FailedTasksCount;
        return totalTasks == 0 ? 0.0 : (double)SuccessfulTasksCount / totalTasks;
    }

    public void AddSpecialization(AgentSpecialization specialization)
    {
        if (specialization == null)
            throw new ArgumentNullException(nameof(specialization));

        var existing = _specializations.FirstOrDefault(s => 
            s.Domain == specialization.Domain && s.Subdomain == specialization.Subdomain);

        if (existing != null)
        {
            _specializations.Remove(existing);
        }

        _specializations.Add(specialization);
        UpdateTimestamp();
    }

    public void RemoveSpecialization(string domain, string subdomain)
    {
        var existing = _specializations.FirstOrDefault(s => 
            s.Domain.Equals(domain, StringComparison.OrdinalIgnoreCase) && 
            s.Subdomain.Equals(subdomain, StringComparison.OrdinalIgnoreCase));

        if (existing != null)
        {
            _specializations.Remove(existing);
            UpdateTimestamp();
        }
    }

    public AgentSpecialization? GetSpecialization(string domain, string subdomain)
    {
        return _specializations.FirstOrDefault(s => 
            s.Domain.Equals(domain, StringComparison.OrdinalIgnoreCase) && 
            s.Subdomain.Equals(subdomain, StringComparison.OrdinalIgnoreCase));
    }

    public IEnumerable<AgentSpecialization> GetSpecializationsByDomain(string domain)
    {
        return _specializations.Where(s => 
            s.Domain.Equals(domain, StringComparison.OrdinalIgnoreCase));
    }

    public void RecordSpecializationUsage(string domain, string subdomain)
    {
        var specialization = GetSpecialization(domain, subdomain);
        if (specialization != null)
        {
            var updated = specialization.RecordUsage();
            _specializations.Remove(specialization);
            _specializations.Add(updated);
            UpdateTimestamp();
        }
    }

    public double CalculateTaskRelevanceScore(string domain, string? subdomain = null, IEnumerable<string>? keywords = null)
    {
        if (!_specializations.Any())
            return 0.0;

        var relevanceScores = _specializations
            .Select(s => s.CalculateRelevanceScore(domain, subdomain, keywords))
            .ToList();

        return relevanceScores.Any() ? relevanceScores.Max() : 0.0;
    }

    public bool HasSpecializationIn(string domain)
    {
        return _specializations.Any(s => 
            s.Domain.Equals(domain, StringComparison.OrdinalIgnoreCase));
    }

    public int GetAverageSkillLevel()
    {
        return _specializations.Any() 
            ? (int)Math.Round(_specializations.Average(s => s.SkillLevel))
            : 0;
    }

    public double GetAverageConfidence()
    {
        return _specializations.Any() 
            ? _specializations.Average(s => s.Confidence)
            : 0.0;
    }

    private void UpdateAverageResponseTime(TimeSpan newResponseTime)
    {
        if (SuccessfulTasksCount == 1)
        {
            AverageResponseTime = newResponseTime.TotalMilliseconds;
        }
        else
        {
            AverageResponseTime = (AverageResponseTime * (SuccessfulTasksCount - 1) + 
                                 newResponseTime.TotalMilliseconds) / SuccessfulTasksCount;
        }
    }
}