namespace HeyDav.Domain.Workflows.Enums;

public enum WorkflowCategory
{
    DailyPlanning,
    EmailManagement,
    MeetingPreparation,
    ProjectManagement,
    WeeklyReview,
    ContentCreation,
    Learning,
    PersonalDevelopment,
    HealthWellness,
    Financial,
    Communication,
    Research,
    TaskManagement,
    GoalSetting,
    HabitBuilding,
    TimeManagement,
    Other
}

public enum WorkflowDifficulty
{
    Beginner,
    Intermediate,
    Advanced,
    Expert
}

public enum WorkflowStatus
{
    NotStarted,
    Running,
    Paused,
    Completed,
    Cancelled,
    Failed
}

public enum WorkflowStepType
{
    Information,        // Display information to user
    Input,             // Collect input from user
    Decision,          // User makes a decision
    Action,            // Perform an action
    Automation,        // Automated task
    Integration,       // External system integration
    Timer,             // Time-based step
    Reminder,          // Reminder or notification
    Review,            // Review and validation
    Checkpoint         // Progress checkpoint
}

public enum WorkflowStepStatus
{
    NotStarted,
    Running,
    Paused,
    Completed,
    Failed,
    Cancelled,
    Skipped
}

public enum TriggerType
{
    Manual,
    Scheduled,
    Event,
    Conditional,
    Webhook,
    Integration
}

public enum HabitType
{
    Positive,    // Building good habits
    Negative,    // Breaking bad habits
    Neutral      // Tracking habits
}

public enum HabitFrequency
{
    Daily,
    Weekly,
    Monthly,
    Custom
}

public enum HabitPriority
{
    Low,
    Medium,
    High,
    Critical
}

public enum SchedulingStrategy
{
    EnergyBased,      // Schedule based on energy levels
    PriorityBased,    // Schedule based on task priority
    DeadlineBased,    // Schedule based on due dates
    Optimal,          // AI-optimized scheduling
    Manual,           // User-defined scheduling
    Balanced          // Balance of all factors
}

public enum ProductivityMetric
{
    TasksCompleted,
    TimeSpent,
    GoalsAchieved,
    HabitsCompleted,
    EnergyLevel,
    MoodScore,
    FocusTime,
    InterruptionCount,
    EfficiencyRatio
}