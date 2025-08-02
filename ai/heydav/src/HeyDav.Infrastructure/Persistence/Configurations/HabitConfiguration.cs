using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;
using HeyDav.Domain.Workflows.Entities;
using HeyDav.Domain.Workflows.Enums;
using System.Text.Json;

namespace HeyDav.Infrastructure.Persistence.Configurations;

public class HabitConfiguration : IEntityTypeConfiguration<Habit>
{
    public void Configure(EntityTypeBuilder<Habit> builder)
    {
        builder.ToTable("Habits");

        builder.HasKey(x => x.Id);

        builder.Property(x => x.Name)
            .IsRequired()
            .HasMaxLength(200);

        builder.Property(x => x.Description)
            .IsRequired()
            .HasMaxLength(1000);

        builder.Property(x => x.Type)
            .HasConversion<int>()
            .IsRequired();

        builder.Property(x => x.Frequency)
            .HasConversion<int>()
            .IsRequired();

        builder.Property(x => x.TargetDuration);

        builder.Property(x => x.TargetCount);

        builder.Property(x => x.TargetUnit)
            .HasMaxLength(50);

        builder.Property(x => x.IsActive)
            .IsRequired()
            .HasDefaultValue(true);

        builder.Property(x => x.StartDate)
            .IsRequired();

        builder.Property(x => x.EndDate);

        builder.Property(x => x.Priority)
            .HasConversion<int>()
            .IsRequired();

        builder.Property(x => x.Reminder)
            .HasColumnType("TEXT");

        builder.Property(x => x.CurrentStreak)
            .IsRequired()
            .HasDefaultValue(0);

        builder.Property(x => x.LongestStreak)
            .IsRequired()
            .HasDefaultValue(0);

        builder.Property(x => x.CompletionRate)
            .HasPrecision(5, 2)
            .HasDefaultValue(0.0m);

        // Configure Tags collection as JSON
        builder.Property(x => x.Tags)
            .HasConversion(
                v => JsonSerializer.Serialize(v, (JsonSerializerOptions?)null),
                v => JsonSerializer.Deserialize<List<string>>(v, (JsonSerializerOptions?)null) ?? new List<string>())
            .HasColumnName("Tags")
            .HasColumnType("TEXT");

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
        builder.HasIndex(x => x.Type);
        builder.HasIndex(x => x.Frequency);
        builder.HasIndex(x => x.IsActive);
        builder.HasIndex(x => x.StartDate);
        builder.HasIndex(x => x.Priority);
        builder.HasIndex(x => x.CurrentStreak);
        builder.HasIndex(x => x.CompletionRate);
        builder.HasIndex(x => x.IsDeleted);

        // Configure relationship with habit entries
        builder.HasMany(x => x.Entries)
            .WithOne()
            .HasForeignKey("HabitId")
            .OnDelete(DeleteBehavior.Cascade);
    }
}

public class HabitEntryConfiguration : IEntityTypeConfiguration<HabitEntry>
{
    public void Configure(EntityTypeBuilder<HabitEntry> builder)
    {
        builder.ToTable("HabitEntries");

        builder.HasKey(x => x.Id);

        builder.Property(x => x.HabitId)
            .IsRequired();

        builder.Property(x => x.Date)
            .IsRequired()
            .HasColumnType("DATE");

        builder.Property(x => x.IsCompleted)
            .IsRequired()
            .HasDefaultValue(false);

        builder.Property(x => x.ActualDuration);

        builder.Property(x => x.ActualCount);

        builder.Property(x => x.Notes)
            .HasColumnType("TEXT");

        builder.Property(x => x.Mood)
            .HasPrecision(3, 1)
            .HasDefaultValue(5.0m);

        builder.Property(x => x.Energy)
            .HasPrecision(3, 1)
            .HasDefaultValue(5.0m);

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
        builder.HasIndex(x => x.HabitId);
        builder.HasIndex(x => x.Date);
        builder.HasIndex(x => x.IsCompleted);
        builder.HasIndex(x => x.Mood);
        builder.HasIndex(x => x.Energy);
        builder.HasIndex(x => x.IsDeleted);

        // Composite index for unique constraint
        builder.HasIndex(x => new { x.HabitId, x.Date })
            .IsUnique();

        // Foreign key relationship
        builder.HasOne<Habit>()
            .WithMany()
            .HasForeignKey(x => x.HabitId)
            .OnDelete(DeleteBehavior.Cascade);
    }
}