using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;
using HeyDav.Domain.TodoManagement.Entities;
using HeyDav.Domain.TodoManagement.Enums;
using HeyDav.Domain.TodoManagement.ValueObjects;
using System.Text.Json;

namespace HeyDav.Infrastructure.Persistence.Configurations;

public class TodoItemConfiguration : IEntityTypeConfiguration<TodoItem>
{
    public void Configure(EntityTypeBuilder<TodoItem> builder)
    {
        builder.ToTable("TodoItems");

        builder.HasKey(x => x.Id);

        builder.Property(x => x.Title)
            .IsRequired()
            .HasMaxLength(200);

        builder.Property(x => x.Description)
            .HasMaxLength(1000);

        builder.Property(x => x.Priority)
            .HasConversion<int>();

        builder.Property(x => x.Status)
            .HasConversion<int>();

        builder.Property(x => x.DueDate)
            .IsRequired(false);

        builder.Property(x => x.ScheduledDate)
            .IsRequired(false);

        builder.Property(x => x.CompletedDate)
            .IsRequired(false);

        builder.Property(x => x.EstimatedDuration)
            .IsRequired(false);

        builder.Property(x => x.ActualDuration)
            .IsRequired(false);

        builder.Property(x => x.CategoryId)
            .IsRequired(false);

        builder.Property(x => x.ParentId)
            .IsRequired(false);

        builder.Property(x => x.GoalId)
            .IsRequired(false);

        builder.Property(x => x.EnergyLevel)
            .IsRequired(false);

        // Configure RecurrencePattern as owned entity
        builder.OwnsOne(x => x.RecurrencePattern, rp =>
        {
            rp.Property(p => p.Type)
                .HasConversion<int>()
                .HasColumnName("RecurrenceType");

            rp.Property(p => p.Interval)
                .HasColumnName("RecurrenceInterval");

            rp.Property(p => p.DayOfMonth)
                .HasColumnName("RecurrenceDayOfMonth");

            rp.Property(p => p.EndDate)
                .HasColumnName("RecurrenceEndDate");

            rp.Property(p => p.MaxOccurrences)
                .HasColumnName("RecurrenceMaxOccurrences");

            rp.Property(p => p.DaysOfWeek)
                .HasConversion(
                    v => v != null ? JsonSerializer.Serialize(v, (JsonSerializerOptions?)null) : null,
                    v => v != null ? JsonSerializer.Deserialize<DayOfWeek[]>(v, (JsonSerializerOptions?)null) : null)
                .HasColumnName("RecurrenceDaysOfWeek");
        });

        // Configure collections as JSON
        builder.Property(x => x.DependencyIds)
            .HasConversion(
                v => JsonSerializer.Serialize(v, (JsonSerializerOptions?)null),
                v => JsonSerializer.Deserialize<List<Guid>>(v, (JsonSerializerOptions?)null) ?? new List<Guid>())
            .HasColumnName("DependencyIds");

        builder.Property(x => x.Tags)
            .HasConversion(
                v => JsonSerializer.Serialize(v, (JsonSerializerOptions?)null),
                v => JsonSerializer.Deserialize<List<string>>(v, (JsonSerializerOptions?)null) ?? new List<string>())
            .HasColumnName("Tags");

        // Base entity properties
        builder.Property(x => x.CreatedAt)
            .IsRequired();

        builder.Property(x => x.UpdatedAt)
            .IsRequired();

        builder.Property(x => x.IsDeleted)
            .IsRequired()
            .HasDefaultValue(false);

        builder.Property(x => x.DeletedAt)
            .IsRequired(false);

        // Indexes
        builder.HasIndex(x => x.Status);
        builder.HasIndex(x => x.Priority);
        builder.HasIndex(x => x.DueDate);
        builder.HasIndex(x => x.ScheduledDate);
        builder.HasIndex(x => x.CategoryId);
        builder.HasIndex(x => x.GoalId);
        builder.HasIndex(x => x.IsDeleted);

        // Foreign key relationships
        builder.HasOne<Category>()
            .WithMany()
            .HasForeignKey(x => x.CategoryId)
            .OnDelete(DeleteBehavior.SetNull);

        builder.HasOne<TodoItem>()
            .WithMany()
            .HasForeignKey(x => x.ParentId)
            .OnDelete(DeleteBehavior.SetNull);
    }
}