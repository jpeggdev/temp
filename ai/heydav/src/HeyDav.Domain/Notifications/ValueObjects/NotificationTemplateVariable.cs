using HeyDav.Domain.Common.Base;

namespace HeyDav.Domain.Notifications.ValueObjects;

public class NotificationTemplateVariable : ValueObject
{
    public string Name { get; private set; } = string.Empty;
    public string Description { get; private set; } = string.Empty;
    public bool IsRequired { get; private set; } = false;
    public string? DefaultValue { get; private set; }
    public string? ValidationPattern { get; private set; }
    public string? DisplayName { get; private set; }
    public string? HelpText { get; private set; }

    private NotificationTemplateVariable() { } // For EF Core

    public NotificationTemplateVariable(
        string name,
        string description,
        bool isRequired = false,
        string? defaultValue = null,
        string? validationPattern = null,
        string? displayName = null,
        string? helpText = null)
    {
        Name = name ?? throw new ArgumentNullException(nameof(name));
        Description = description ?? throw new ArgumentNullException(nameof(description));
        IsRequired = isRequired;
        DefaultValue = defaultValue;
        ValidationPattern = validationPattern;
        DisplayName = displayName;
        HelpText = helpText;
    }

    public NotificationTemplateVariable AsRequired()
    {
        return new NotificationTemplateVariable(
            Name, Description, true, DefaultValue, ValidationPattern, DisplayName, HelpText);
    }

    public NotificationTemplateVariable AsOptional()
    {
        return new NotificationTemplateVariable(
            Name, Description, false, DefaultValue, ValidationPattern, DisplayName, HelpText);
    }

    public NotificationTemplateVariable WithDefaultValue(string defaultValue)
    {
        return new NotificationTemplateVariable(
            Name, Description, IsRequired, defaultValue, ValidationPattern, DisplayName, HelpText);
    }

    public NotificationTemplateVariable WithValidation(string validationPattern)
    {
        return new NotificationTemplateVariable(
            Name, Description, IsRequired, DefaultValue, validationPattern, DisplayName, HelpText);
    }

    public NotificationTemplateVariable WithDisplayName(string displayName)
    {
        return new NotificationTemplateVariable(
            Name, Description, IsRequired, DefaultValue, ValidationPattern, displayName, HelpText);
    }

    public NotificationTemplateVariable WithHelpText(string helpText)
    {
        return new NotificationTemplateVariable(
            Name, Description, IsRequired, DefaultValue, ValidationPattern, DisplayName, helpText);
    }

    public bool IsValid(object? value)
    {
        if (IsRequired && (value == null || string.IsNullOrWhiteSpace(value.ToString())))
        {
            return false;
        }

        if (!string.IsNullOrEmpty(ValidationPattern) && value != null)
        {
            var stringValue = value.ToString();
            if (!string.IsNullOrEmpty(stringValue))
            {
                return System.Text.RegularExpressions.Regex.IsMatch(stringValue, ValidationPattern);
            }
        }

        return true;
    }

    public object? GetValueOrDefault(object? value)
    {
        if (value != null && !string.IsNullOrWhiteSpace(value.ToString()))
        {
            return value;
        }

        return DefaultValue;
    }

    protected override IEnumerable<object> GetEqualityComponents()
    {
        yield return Name;
        yield return Description;
        yield return IsRequired;
        yield return DefaultValue ?? string.Empty;
        yield return ValidationPattern ?? string.Empty;
        yield return DisplayName ?? string.Empty;
        yield return HelpText ?? string.Empty;
    }
}