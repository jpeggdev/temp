namespace HeyDav.Domain.Notifications.Enums;

public enum NotificationType
{
    System = 0,
    TaskReminder = 1,
    TaskDeadline = 2,
    TaskCompleted = 3,
    GoalProgress = 4,
    GoalAchieved = 5,
    GoalMissed = 6,
    MeetingReminder = 7,
    MeetingFollowUp = 8,
    HabitReminder = 9,
    HabitStreak = 10,
    NewsUpdate = 11,
    WeatherAlert = 12,
    CalendarEvent = 13,
    EmailDigest = 14,
    WorkflowUpdate = 15,
    SystemAlert = 16,
    SecurityAlert = 17,
    Custom = 99
}

public enum NotificationPriority
{
    Low = 0,
    Medium = 1,
    High = 2,
    Urgent = 3,
    Critical = 4
}

public enum NotificationStatus
{
    Pending = 0,
    Scheduled = 1,
    Sending = 2,
    Sent = 3,
    Delivered = 4,
    Read = 5,
    Failed = 6,
    Expired = 7,
    Cancelled = 8
}

public enum NotificationChannel
{
    InApp = 0,
    Push = 1,
    Email = 2,
    SMS = 3,
    Desktop = 4,
    Webhook = 5,
    Slack = 6,
    Teams = 7,
    Discord = 8
}

public enum NotificationInteractionType
{
    Viewed = 0,
    Clicked = 1,
    Dismissed = 2,
    ActionExecuted = 3,
    Replied = 4,
    Forwarded = 5,
    Starred = 6,
    Archived = 7,
    Snoozed = 8
}

public enum NotificationGroupingStrategy
{
    None = 0,
    ByType = 1,
    BySource = 2,
    ByPriority = 3,
    ByTime = 4,
    ByRelatedEntity = 5,
    Smart = 6
}

public enum NotificationBatchingStrategy
{
    None = 0,
    TimeWindow = 1,
    Count = 2,
    Priority = 3,
    UserActivity = 4,
    Smart = 5
}

public enum DoNotDisturbMode
{
    Disabled = 0,
    Enabled = 1,
    Schedule = 2,
    Focus = 3,
    Meeting = 4,
    Sleep = 5
}