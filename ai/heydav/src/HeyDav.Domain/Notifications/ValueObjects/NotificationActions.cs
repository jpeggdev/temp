using HeyDav.Domain.Common.Base;

namespace HeyDav.Domain.Notifications.ValueObjects;

public class NotificationActions : ValueObject
{
    public List<NotificationAction> Actions { get; private set; } = new();
    public bool AllowReply { get; private set; } = false;
    public string? ReplyPlaceholder { get; private set; }
    public bool AllowDismiss { get; private set; } = true;
    public bool AllowSnooze { get; private set; } = false;
    public List<TimeSpan> SnoozeOptions { get; private set; } = new();

    public NotificationActions() { }

    public NotificationActions(
        List<NotificationAction>? actions = null,
        bool allowReply = false,
        string? replyPlaceholder = null,
        bool allowDismiss = true,
        bool allowSnooze = false,
        List<TimeSpan>? snoozeOptions = null)
    {
        Actions = actions ?? new List<NotificationAction>();
        AllowReply = allowReply;
        ReplyPlaceholder = replyPlaceholder;
        AllowDismiss = allowDismiss;
        AllowSnooze = allowSnooze;
        SnoozeOptions = snoozeOptions ?? new List<TimeSpan>();
    }

    public NotificationActions WithAction(string id, string title, string? icon = null, bool isPrimary = false)
    {
        var newActions = new List<NotificationAction>(Actions)
        {
            new NotificationAction(id, title, icon, isPrimary)
        };

        return new NotificationActions(
            newActions, AllowReply, ReplyPlaceholder, AllowDismiss, AllowSnooze, SnoozeOptions);
    }

    public NotificationActions WithReply(string placeholder = "Type a reply...")
    {
        return new NotificationActions(
            Actions, true, placeholder, AllowDismiss, AllowSnooze, SnoozeOptions);
    }

    public NotificationActions WithSnooze(params TimeSpan[] snoozeOptions)
    {
        return new NotificationActions(
            Actions, AllowReply, ReplyPlaceholder, AllowDismiss, true, snoozeOptions.ToList());
    }

    public NotificationActions WithoutDismiss()
    {
        return new NotificationActions(
            Actions, AllowReply, ReplyPlaceholder, false, AllowSnooze, SnoozeOptions);
    }

    protected override IEnumerable<object> GetEqualityComponents()
    {
        yield return AllowReply;
        yield return ReplyPlaceholder ?? string.Empty;
        yield return AllowDismiss;
        yield return AllowSnooze;

        foreach (var action in Actions.OrderBy(a => a.Id))
        {
            yield return action;
        }

        foreach (var snoozeOption in SnoozeOptions.OrderBy(s => s))
        {
            yield return snoozeOption;
        }
    }
}

public class NotificationAction : ValueObject
{
    public string Id { get; private set; } = string.Empty;
    public string Title { get; private set; } = string.Empty;
    public string? Icon { get; private set; }
    public bool IsPrimary { get; private set; } = false;
    public Dictionary<string, object> Data { get; private set; } = new();

    private NotificationAction() { } // For EF Core

    public NotificationAction(string id, string title, string? icon = null, bool isPrimary = false)
    {
        Id = id ?? throw new ArgumentNullException(nameof(id));
        Title = title ?? throw new ArgumentNullException(nameof(title));
        Icon = icon;
        IsPrimary = isPrimary;
    }

    public NotificationAction WithData(string key, object value)
    {
        var newData = new Dictionary<string, object>(Data)
        {
            [key] = value
        };

        return new NotificationAction(Id, Title, Icon, IsPrimary)
        {
            Data = newData
        };
    }

    protected override IEnumerable<object> GetEqualityComponents()
    {
        yield return Id;
        yield return Title;
        yield return Icon ?? string.Empty;
        yield return IsPrimary;

        foreach (var kvp in Data.OrderBy(kvp => kvp.Key))
        {
            yield return kvp.Key;
            yield return kvp.Value;
        }
    }
}