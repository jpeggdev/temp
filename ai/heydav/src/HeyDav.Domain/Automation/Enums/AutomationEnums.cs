namespace HeyDav.Domain.Automation.Enums;

public enum AutomationRuleStatus
{
    Active = 0,
    Disabled = 1,
    Paused = 2,
    Draft = 3,
    Archived = 4,
    Error = 5
}

public enum AutomationExecutionStatus
{
    Pending = 0,
    Running = 1,
    Completed = 2,
    Failed = 3,
    Cancelled = 4,
    Timeout = 5
}

public enum AutomationTriggerType
{
    Time = 0,
    Event = 1,
    Condition = 2,
    Manual = 3,
    Webhook = 4,
    FileSystem = 5,
    Database = 6,
    Api = 7,
    User = 8
}

public enum AutomationActionType
{
    SendNotification = 0,
    SendEmail = 1,
    CreateTask = 2,
    UpdateTask = 3,
    CompleteTask = 4,
    CreateGoal = 5,
    UpdateGoal = 6,
    ExecuteCommand = 7,
    CallApi = 8,
    RunScript = 9,
    CreateFile = 10,
    UpdateFile = 11,
    DeleteFile = 12,
    SendWebhook = 13,
    Custom = 99
}

public enum AutomationConditionType
{
    Equals = 0,
    NotEquals = 1,
    GreaterThan = 2,
    LessThan = 3,
    GreaterThanOrEqual = 4,
    LessThanOrEqual = 5,
    Contains = 6,
    NotContains = 7,
    StartsWith = 8,
    EndsWith = 9,
    Matches = 10,
    NotMatches = 11,
    IsNull = 12,
    IsNotNull = 13,
    InRange = 14,
    NotInRange = 15,
    Custom = 99
}

public enum AutomationScheduleType
{
    Once = 0,
    Daily = 1,
    Weekly = 2,
    Monthly = 3,
    Yearly = 4,
    Interval = 5,
    Cron = 6,
    Custom = 7
}

public enum AutomationLogLevel
{
    Debug = 0,
    Information = 1,
    Warning = 2,
    Error = 3,
    Critical = 4
}

public enum AutomationPriority
{
    Low = 0,
    Normal = 1,
    High = 2,
    Critical = 3
}

public enum AutomationRunMode
{
    Sequential = 0,
    Parallel = 1,
    ConditionalParallel = 2
}