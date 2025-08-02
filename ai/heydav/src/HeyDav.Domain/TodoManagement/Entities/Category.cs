using HeyDav.Domain.Common.Base;

namespace HeyDav.Domain.TodoManagement.Entities;

public class Category : BaseEntity
{
    public string Name { get; private set; }
    public string? Description { get; private set; }
    public string Color { get; private set; } // Hex color code
    public string? Icon { get; private set; }
    public int DisplayOrder { get; private set; }
    public bool IsActive { get; private set; }

    private Category(string name, string color, int displayOrder = 0)
    {
        Name = name;
        Color = color;
        DisplayOrder = displayOrder;
        IsActive = true;
    }

    public static Category Create(string name, string color, string? description = null, int displayOrder = 0)
    {
        if (string.IsNullOrWhiteSpace(name))
            throw new ArgumentException("Category name cannot be empty", nameof(name));

        if (string.IsNullOrWhiteSpace(color))
            throw new ArgumentException("Category color cannot be empty", nameof(color));

        var category = new Category(name, color, displayOrder)
        {
            Description = description
        };

        return category;
    }

    public void Update(string name, string color, string? description = null)
    {
        if (string.IsNullOrWhiteSpace(name))
            throw new ArgumentException("Category name cannot be empty", nameof(name));

        if (string.IsNullOrWhiteSpace(color))
            throw new ArgumentException("Category color cannot be empty", nameof(color));

        Name = name;
        Color = color;
        Description = description;
        UpdateTimestamp();
    }

    public void SetIcon(string? icon)
    {
        Icon = icon;
        UpdateTimestamp();
    }

    public void SetDisplayOrder(int order)
    {
        DisplayOrder = order;
        UpdateTimestamp();
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
}