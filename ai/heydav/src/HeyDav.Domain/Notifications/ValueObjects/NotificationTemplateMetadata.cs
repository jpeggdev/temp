using HeyDav.Domain.Common.Base;

namespace HeyDav.Domain.Notifications.ValueObjects;

public class NotificationTemplateMetadata : ValueObject
{
    public string Version { get; private set; } = "1.0";
    public string? Author { get; private set; }
    public DateTime CreatedAt { get; private set; } = DateTime.UtcNow;
    public DateTime UpdatedAt { get; private set; } = DateTime.UtcNow;
    public List<string> SupportedChannels { get; private set; } = new();
    public Dictionary<string, object> SchemaValidation { get; private set; } = new();
    public string? PreviewData { get; private set; }
    public List<string> Dependencies { get; private set; } = new();
    public string? Documentation { get; private set; }
    public Dictionary<string, string> Localizations { get; private set; } = new();

    public NotificationTemplateMetadata() { }

    public NotificationTemplateMetadata(
        string version = "1.0",
        string? author = null,
        List<string>? supportedChannels = null,
        Dictionary<string, object>? schemaValidation = null,
        string? previewData = null,
        List<string>? dependencies = null,
        string? documentation = null,
        Dictionary<string, string>? localizations = null)
    {
        Version = version;
        Author = author;
        SupportedChannels = supportedChannels ?? new List<string>();
        SchemaValidation = schemaValidation ?? new Dictionary<string, object>();
        PreviewData = previewData;
        Dependencies = dependencies ?? new List<string>();
        Documentation = documentation;
        Localizations = localizations ?? new Dictionary<string, string>();
        UpdatedAt = DateTime.UtcNow;
    }

    public NotificationTemplateMetadata WithVersion(string version)
    {
        return new NotificationTemplateMetadata(
            version, Author, SupportedChannels, SchemaValidation, PreviewData, 
            Dependencies, Documentation, Localizations);
    }

    public NotificationTemplateMetadata WithAuthor(string author)
    {
        return new NotificationTemplateMetadata(
            Version, author, SupportedChannels, SchemaValidation, PreviewData, 
            Dependencies, Documentation, Localizations);
    }

    public NotificationTemplateMetadata WithSupportedChannels(params string[] channels)
    {
        return new NotificationTemplateMetadata(
            Version, Author, channels.ToList(), SchemaValidation, PreviewData, 
            Dependencies, Documentation, Localizations);
    }

    public NotificationTemplateMetadata WithPreviewData(string previewData)
    {
        return new NotificationTemplateMetadata(
            Version, Author, SupportedChannels, SchemaValidation, previewData, 
            Dependencies, Documentation, Localizations);
    }

    public NotificationTemplateMetadata WithDocumentation(string documentation)
    {
        return new NotificationTemplateMetadata(
            Version, Author, SupportedChannels, SchemaValidation, PreviewData, 
            Dependencies, documentation, Localizations);
    }

    public NotificationTemplateMetadata WithDependency(string dependency)
    {
        var newDependencies = new List<string>(Dependencies);
        if (!newDependencies.Contains(dependency))
        {
            newDependencies.Add(dependency);
        }

        return new NotificationTemplateMetadata(
            Version, Author, SupportedChannels, SchemaValidation, PreviewData, 
            newDependencies, Documentation, Localizations);
    }

    public NotificationTemplateMetadata WithLocalization(string language, string content)
    {
        var newLocalizations = new Dictionary<string, string>(Localizations)
        {
            [language] = content
        };

        return new NotificationTemplateMetadata(
            Version, Author, SupportedChannels, SchemaValidation, PreviewData, 
            Dependencies, Documentation, newLocalizations);
    }

    public NotificationTemplateMetadata WithSchemaValidation(string property, object validation)
    {
        var newSchemaValidation = new Dictionary<string, object>(SchemaValidation)
        {
            [property] = validation
        };

        return new NotificationTemplateMetadata(
            Version, Author, SupportedChannels, newSchemaValidation, PreviewData, 
            Dependencies, Documentation, Localizations);
    }

    public bool SupportsChannel(string channel)
    {
        return SupportedChannels.Contains(channel, StringComparer.OrdinalIgnoreCase);
    }

    public string? GetLocalizedContent(string language)
    {
        return Localizations.TryGetValue(language, out var content) ? content : null;
    }

    public bool HasDependency(string dependency)
    {
        return Dependencies.Contains(dependency, StringComparer.OrdinalIgnoreCase);
    }

    protected override IEnumerable<object> GetEqualityComponents()
    {
        yield return Version;
        yield return Author ?? string.Empty;
        yield return CreatedAt;
        yield return PreviewData ?? string.Empty;
        yield return Documentation ?? string.Empty;

        foreach (var channel in SupportedChannels.OrderBy(c => c))
        {
            yield return channel;
        }

        foreach (var dependency in Dependencies.OrderBy(d => d))
        {
            yield return dependency;
        }

        foreach (var kvp in SchemaValidation.OrderBy(kvp => kvp.Key))
        {
            yield return kvp.Key;
            yield return kvp.Value;
        }

        foreach (var kvp in Localizations.OrderBy(kvp => kvp.Key))
        {
            yield return kvp.Key;
            yield return kvp.Value;
        }
    }
}