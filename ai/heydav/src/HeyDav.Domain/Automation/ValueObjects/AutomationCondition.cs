using HeyDav.Domain.Common.Base;
using HeyDav.Domain.Automation.Enums;
using System.Text.RegularExpressions;

namespace HeyDav.Domain.Automation.ValueObjects;

public class AutomationCondition : ValueObject
{
    public Guid Id { get; private set; } = Guid.NewGuid();
    public AutomationConditionType Type { get; private set; }
    public string Name { get; private set; } = string.Empty;
    public string Description { get; private set; } = string.Empty;
    public string Field { get; private set; } = string.Empty;
    public object? Value { get; private set; }
    public object? SecondaryValue { get; private set; } // For range conditions
    public bool IsEnabled { get; private set; } = true;
    public int Order { get; private set; } = 0;
    public Dictionary<string, object> Configuration { get; private set; } = new();

    private AutomationCondition() { } // For EF Core

    public AutomationCondition(
        AutomationConditionType type,
        string name,
        string field,
        object? value = null,
        object? secondaryValue = null,
        string description = "",
        int order = 0,
        Dictionary<string, object>? configuration = null)
    {
        Type = type;
        Name = name ?? throw new ArgumentNullException(nameof(name));
        Field = field ?? throw new ArgumentNullException(nameof(field));
        Value = value;
        SecondaryValue = secondaryValue;
        Description = description;
        Order = order;
        Configuration = configuration ?? new Dictionary<string, object>();
    }

    public AutomationCondition WithValue(object? value)
    {
        return new AutomationCondition(Type, Name, Field, value, SecondaryValue, Description, Order, Configuration);
    }

    public AutomationCondition WithRange(object? minValue, object? maxValue)
    {
        return new AutomationCondition(AutomationConditionType.InRange, Name, Field, minValue, maxValue, Description, Order, Configuration);
    }

    public AutomationCondition WithOrder(int order)
    {
        return new AutomationCondition(Type, Name, Field, Value, SecondaryValue, Description, order, Configuration);
    }

    public AutomationCondition WithConfiguration(string key, object value)
    {
        var newConfiguration = new Dictionary<string, object>(Configuration)
        {
            [key] = value
        };

        return new AutomationCondition(Type, Name, Field, Value, SecondaryValue, Description, Order, newConfiguration);
    }

    public AutomationCondition Disable()
    {
        return new AutomationCondition(Type, Name, Field, Value, SecondaryValue, Description, Order, Configuration)
        {
            IsEnabled = false
        };
    }

    public AutomationCondition Enable()
    {
        return new AutomationCondition(Type, Name, Field, Value, SecondaryValue, Description, Order, Configuration)
        {
            IsEnabled = true
        };
    }

    public bool Evaluate(Dictionary<string, object> context)
    {
        if (!IsEnabled) return true;

        if (!context.TryGetValue(Field, out var fieldValue))
        {
            return Type == AutomationConditionType.IsNull;
        }

        return Type switch
        {
            AutomationConditionType.Equals => AreEqual(fieldValue, Value),
            AutomationConditionType.NotEquals => !AreEqual(fieldValue, Value),
            AutomationConditionType.GreaterThan => IsGreaterThan(fieldValue, Value),
            AutomationConditionType.LessThan => IsLessThan(fieldValue, Value),
            AutomationConditionType.GreaterThanOrEqual => IsGreaterThanOrEqual(fieldValue, Value),
            AutomationConditionType.LessThanOrEqual => IsLessThanOrEqual(fieldValue, Value),
            AutomationConditionType.Contains => Contains(fieldValue, Value),
            AutomationConditionType.NotContains => !Contains(fieldValue, Value),
            AutomationConditionType.StartsWith => StartsWith(fieldValue, Value),
            AutomationConditionType.EndsWith => EndsWith(fieldValue, Value),
            AutomationConditionType.Matches => Matches(fieldValue, Value),
            AutomationConditionType.NotMatches => !Matches(fieldValue, Value),
            AutomationConditionType.IsNull => fieldValue == null,
            AutomationConditionType.IsNotNull => fieldValue != null,
            AutomationConditionType.InRange => IsInRange(fieldValue, Value, SecondaryValue),
            AutomationConditionType.NotInRange => !IsInRange(fieldValue, Value, SecondaryValue),
            AutomationConditionType.Custom => EvaluateCustomCondition(fieldValue, context),
            _ => false
        };
    }

    private bool AreEqual(object? fieldValue, object? expectedValue)
    {
        if (fieldValue == null && expectedValue == null) return true;
        if (fieldValue == null || expectedValue == null) return false;
        
        return fieldValue.ToString()?.Equals(expectedValue.ToString(), StringComparison.OrdinalIgnoreCase) ?? false;
    }

    private bool IsGreaterThan(object? fieldValue, object? compareValue)
    {
        return CompareValues(fieldValue, compareValue) > 0;
    }

    private bool IsLessThan(object? fieldValue, object? compareValue)
    {
        return CompareValues(fieldValue, compareValue) < 0;
    }

    private bool IsGreaterThanOrEqual(object? fieldValue, object? compareValue)
    {
        return CompareValues(fieldValue, compareValue) >= 0;
    }

    private bool IsLessThanOrEqual(object? fieldValue, object? compareValue)
    {
        return CompareValues(fieldValue, compareValue) <= 0;
    }

    private int CompareValues(object? value1, object? value2)
    {
        if (value1 == null && value2 == null) return 0;
        if (value1 == null) return -1;
        if (value2 == null) return 1;

        // Try to compare as numbers first
        if (double.TryParse(value1.ToString(), out var num1) && double.TryParse(value2.ToString(), out var num2))
        {
            return num1.CompareTo(num2);
        }

        // Try to compare as dates
        if (DateTime.TryParse(value1.ToString(), out var date1) && DateTime.TryParse(value2.ToString(), out var date2))
        {
            return date1.CompareTo(date2);
        }

        // Fall back to string comparison
        return string.Compare(value1.ToString(), value2.ToString(), StringComparison.OrdinalIgnoreCase);
    }

    private bool Contains(object? fieldValue, object? searchValue)
    {
        if (fieldValue == null || searchValue == null) return false;
        return fieldValue.ToString()?.Contains(searchValue.ToString() ?? "", StringComparison.OrdinalIgnoreCase) ?? false;
    }

    private bool StartsWith(object? fieldValue, object? prefixValue)
    {
        if (fieldValue == null || prefixValue == null) return false;
        return fieldValue.ToString()?.StartsWith(prefixValue.ToString() ?? "", StringComparison.OrdinalIgnoreCase) ?? false;
    }

    private bool EndsWith(object? fieldValue, object? suffixValue)
    {
        if (fieldValue == null || suffixValue == null) return false;
        return fieldValue.ToString()?.EndsWith(suffixValue.ToString() ?? "", StringComparison.OrdinalIgnoreCase) ?? false;
    }

    private bool Matches(object? fieldValue, object? patternValue)
    {
        if (fieldValue == null || patternValue == null) return false;
        
        try
        {
            var pattern = patternValue.ToString() ?? "";
            var input = fieldValue.ToString() ?? "";
            return Regex.IsMatch(input, pattern, RegexOptions.IgnoreCase);
        }
        catch
        {
            return false;
        }
    }

    private bool IsInRange(object? fieldValue, object? minValue, object? maxValue)
    {
        if (fieldValue == null) return false;
        
        var compareToMin = minValue != null ? CompareValues(fieldValue, minValue) : 1;
        var compareToMax = maxValue != null ? CompareValues(fieldValue, maxValue) : -1;
        
        return compareToMin >= 0 && compareToMax <= 0;
    }

    private bool EvaluateCustomCondition(object? fieldValue, Dictionary<string, object> context)
    {
        // This would be implemented based on custom condition logic
        // For now, return true as a placeholder
        return true;
    }

    // Factory methods for common conditions
    public static AutomationCondition CreateEqualsCondition(string name, string field, object value)
    {
        return new AutomationCondition(AutomationConditionType.Equals, name, field, value);
    }

    public static AutomationCondition CreateContainsCondition(string name, string field, string searchValue)
    {
        return new AutomationCondition(AutomationConditionType.Contains, name, field, searchValue);
    }

    public static AutomationCondition CreateRangeCondition(string name, string field, object minValue, object maxValue)
    {
        return new AutomationCondition(AutomationConditionType.InRange, name, field, minValue, maxValue);
    }

    public static AutomationCondition CreateRegexCondition(string name, string field, string pattern)
    {
        return new AutomationCondition(AutomationConditionType.Matches, name, field, pattern);
    }

    public static AutomationCondition CreateNullCheckCondition(string name, string field, bool shouldBeNull = true)
    {
        var type = shouldBeNull ? AutomationConditionType.IsNull : AutomationConditionType.IsNotNull;
        return new AutomationCondition(type, name, field);
    }

    protected override IEnumerable<object> GetEqualityComponents()
    {
        yield return Id;
        yield return Type;
        yield return Name;
        yield return Description;
        yield return Field;
        yield return Value?.ToString() ?? string.Empty;
        yield return SecondaryValue?.ToString() ?? string.Empty;
        yield return IsEnabled;
        yield return Order;

        foreach (var kvp in Configuration.OrderBy(kvp => kvp.Key))
        {
            yield return kvp.Key;
            yield return kvp.Value;
        }
    }
}