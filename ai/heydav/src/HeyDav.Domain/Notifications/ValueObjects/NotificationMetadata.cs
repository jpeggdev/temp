using HeyDav.Domain.Common.Base;

namespace HeyDav.Domain.Notifications.ValueObjects;

public class NotificationMetadata : ValueObject
{
    public string? ImageUrl { get; private set; }
    public string? IconUrl { get; private set; }
    public string? Sound { get; private set; }
    public string? Color { get; private set; }
    public Dictionary<string, object> CustomData { get; private set; } = new();
    public string? DeepLink { get; private set; }
    public string? Category { get; private set; }
    public List<string> Tags { get; private set; } = new();
    public int? BadgeCount { get; private set; }
    public bool RequiresInteraction { get; private set; } = false;
    public bool Silent { get; private set; } = false;

    public NotificationMetadata() { }

    public NotificationMetadata(
        string? imageUrl = null,
        string? iconUrl = null,
        string? sound = null,
        string? color = null,
        Dictionary<string, object>? customData = null,
        string? deepLink = null,
        string? category = null,
        List<string>? tags = null,
        int? badgeCount = null,
        bool requiresInteraction = false,
        bool silent = false)
    {
        ImageUrl = imageUrl;
        IconUrl = iconUrl;
        Sound = sound;
        Color = color;
        CustomData = customData ?? new Dictionary<string, object>();
        DeepLink = deepLink;
        Category = category;
        Tags = tags ?? new List<string>();
        BadgeCount = badgeCount;
        RequiresInteraction = requiresInteraction;
        Silent = silent;
    }

    public NotificationMetadata WithImage(string imageUrl)
    {
        return new NotificationMetadata(
            imageUrl, IconUrl, Sound, Color, CustomData, DeepLink, Category, Tags, BadgeCount, RequiresInteraction, Silent);
    }

    public NotificationMetadata WithIcon(string iconUrl)
    {
        return new NotificationMetadata(
            ImageUrl, iconUrl, Sound, Color, CustomData, DeepLink, Category, Tags, BadgeCount, RequiresInteraction, Silent);
    }

    public NotificationMetadata WithSound(string sound)
    {
        return new NotificationMetadata(
            ImageUrl, IconUrl, sound, Color, CustomData, DeepLink, Category, Tags, BadgeCount, RequiresInteraction, Silent);
    }

    public NotificationMetadata WithColor(string color)
    {
        return new NotificationMetadata(
            ImageUrl, IconUrl, Sound, color, CustomData, DeepLink, Category, Tags, BadgeCount, RequiresInteraction, Silent);
    }

    public NotificationMetadata WithDeepLink(string deepLink)
    {
        return new NotificationMetadata(
            ImageUrl, IconUrl, Sound, Color, CustomData, deepLink, Category, Tags, BadgeCount, RequiresInteraction, Silent);
    }

    public NotificationMetadata WithCategory(string category)
    {
        return new NotificationMetadata(
            ImageUrl, IconUrl, Sound, Color, CustomData, DeepLink, category, Tags, BadgeCount, RequiresInteraction, Silent);
    }

    public NotificationMetadata WithCustomData(string key, object value)
    {
        var newCustomData = new Dictionary<string, object>(CustomData)
        {
            [key] = value
        };
        
        return new NotificationMetadata(
            ImageUrl, IconUrl, Sound, Color, newCustomData, DeepLink, Category, Tags, BadgeCount, RequiresInteraction, Silent);
    }

    public NotificationMetadata WithTag(string tag)
    {
        var newTags = new List<string>(Tags);
        if (!newTags.Contains(tag))
        {
            newTags.Add(tag);
        }
        
        return new NotificationMetadata(
            ImageUrl, IconUrl, Sound, Color, CustomData, DeepLink, Category, newTags, BadgeCount, RequiresInteraction, Silent);
    }

    public NotificationMetadata WithBadgeCount(int badgeCount)
    {
        return new NotificationMetadata(
            ImageUrl, IconUrl, Sound, Color, CustomData, DeepLink, Category, Tags, badgeCount, RequiresInteraction, Silent);
    }

    public NotificationMetadata WithInteractionRequired(bool requiresInteraction = true)
    {
        return new NotificationMetadata(
            ImageUrl, IconUrl, Sound, Color, CustomData, DeepLink, Category, Tags, BadgeCount, requiresInteraction, Silent);
    }

    public NotificationMetadata AsSilent(bool silent = true)
    {
        return new NotificationMetadata(
            ImageUrl, IconUrl, Sound, Color, CustomData, DeepLink, Category, Tags, BadgeCount, RequiresInteraction, silent);
    }

    protected override IEnumerable<object> GetEqualityComponents()
    {
        yield return ImageUrl ?? string.Empty;
        yield return IconUrl ?? string.Empty;
        yield return Sound ?? string.Empty;
        yield return Color ?? string.Empty;
        yield return DeepLink ?? string.Empty;
        yield return Category ?? string.Empty;
        yield return BadgeCount ?? 0;
        yield return RequiresInteraction;
        yield return Silent;
        
        foreach (var tag in Tags.OrderBy(t => t))
        {
            yield return tag;
        }
        
        foreach (var kvp in CustomData.OrderBy(kvp => kvp.Key))
        {
            yield return kvp.Key;
            yield return kvp.Value;
        }
    }
}