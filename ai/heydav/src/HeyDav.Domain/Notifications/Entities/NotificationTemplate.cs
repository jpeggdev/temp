using HeyDav.Domain.Common.Base;
using HeyDav.Domain.Notifications.Enums;
using HeyDav.Domain.Notifications.ValueObjects;

namespace HeyDav.Domain.Notifications.Entities;

public class NotificationTemplate : BaseEntity
{
    public string Name { get; private set; } = string.Empty;
    public string Description { get; private set; } = string.Empty;
    public NotificationType Type { get; private set; }
    public string TitleTemplate { get; private set; } = string.Empty;
    public string ContentTemplate { get; private set; } = string.Empty;
    
    public NotificationPriority DefaultPriority { get; private set; } = NotificationPriority.Medium;
    public NotificationChannel DefaultChannel { get; private set; } = NotificationChannel.InApp;
    
    public NotificationTemplateMetadata Metadata { get; private set; } = new();
    public List<NotificationTemplateVariable> Variables { get; private set; } = new();
    public NotificationActions DefaultActions { get; private set; } = new();
    
    public bool IsActive { get; private set; } = true;
    public string? Category { get; private set; }
    public List<string> Tags { get; private set; } = new();

    private NotificationTemplate() { } // For EF Core

    public NotificationTemplate(
        string name,
        string description,
        NotificationType type,
        string titleTemplate,
        string contentTemplate,
        NotificationPriority defaultPriority = NotificationPriority.Medium,
        NotificationChannel defaultChannel = NotificationChannel.InApp,
        string? category = null)
    {
        Name = name ?? throw new ArgumentNullException(nameof(name));
        Description = description ?? throw new ArgumentNullException(nameof(description));
        Type = type;
        TitleTemplate = titleTemplate ?? throw new ArgumentNullException(nameof(titleTemplate));
        ContentTemplate = contentTemplate ?? throw new ArgumentNullException(nameof(contentTemplate));
        DefaultPriority = defaultPriority;
        DefaultChannel = defaultChannel;
        Category = category;
    }

    public void UpdateTemplate(string titleTemplate, string contentTemplate)
    {
        TitleTemplate = titleTemplate ?? throw new ArgumentNullException(nameof(titleTemplate));
        ContentTemplate = contentTemplate ?? throw new ArgumentNullException(nameof(contentTemplate));
        UpdateTimestamp();
    }

    public void UpdateDescription(string description)
    {
        Description = description ?? throw new ArgumentNullException(nameof(description));
        UpdateTimestamp();
    }

    public void UpdateDefaults(NotificationPriority priority, NotificationChannel channel)
    {
        DefaultPriority = priority;
        DefaultChannel = channel;
        UpdateTimestamp();
    }

    public void AddVariable(string name, string description, bool isRequired = false, string? defaultValue = null)
    {
        var variable = new NotificationTemplateVariable(name, description, isRequired, defaultValue);
        Variables.Add(variable);
        UpdateTimestamp();
    }

    public void RemoveVariable(string name)
    {
        Variables.RemoveAll(v => v.Name == name);
        UpdateTimestamp();
    }

    public void UpdateActions(NotificationActions actions)
    {
        DefaultActions = actions ?? throw new ArgumentNullException(nameof(actions));
        UpdateTimestamp();
    }

    public void AddTag(string tag)
    {
        if (!Tags.Contains(tag))
        {
            Tags.Add(tag);
            UpdateTimestamp();
        }
    }

    public void RemoveTag(string tag)
    {
        if (Tags.Remove(tag))
        {
            UpdateTimestamp();
        }
    }

    public void Activate()
    {
        IsActive = true;
        UpdateTimestamp();
    }

    public void Deactivate()
    {
        IsActive = false;
        UpdateTimestamp();
    }

    public void UpdateCategory(string? category)
    {
        Category = category;
        UpdateTimestamp();
    }

    public string RenderTitle(Dictionary<string, object> variables)
    {
        return RenderTemplate(TitleTemplate, variables);
    }

    public string RenderContent(Dictionary<string, object> variables)
    {
        return RenderTemplate(ContentTemplate, variables);
    }

    private string RenderTemplate(string template, Dictionary<string, object> variables)
    {
        var result = template;
        
        foreach (var variable in variables)
        {
            var placeholder = $"{{{variable.Key}}}";
            result = result.Replace(placeholder, variable.Value?.ToString() ?? string.Empty);
        }
        
        return result;
    }

    public bool ValidateVariables(Dictionary<string, object> variables)
    {
        var requiredVariables = Variables.Where(v => v.IsRequired).Select(v => v.Name);
        return requiredVariables.All(required => variables.ContainsKey(required));
    }
}