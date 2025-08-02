using Microsoft.EntityFrameworkCore;
using HeyDav.Domain.Workflows.Entities;
using HeyDav.Application.Workflows.Interfaces;
using HeyDav.Infrastructure.Persistence;

namespace HeyDav.Infrastructure.Productivity;

public class HabitRepository : IHabitRepository
{
    private readonly HeyDavDbContext _context;

    public HabitRepository(HeyDavDbContext context)
    {
        _context = context;
    }

    public async Task<Habit?> GetByIdAsync(Guid id, CancellationToken cancellationToken = default)
    {
        return await _context.Habits
            .Include(h => h.Entries)
            .FirstOrDefaultAsync(h => h.Id == id, cancellationToken);
    }

    public async Task<List<Habit>> GetByUserIdAsync(string userId, bool includeInactive = false, CancellationToken cancellationToken = default)
    {
        var query = _context.Habits
            .Include(h => h.Entries)
            .AsQueryable();

        // Note: In a real implementation, you'd have a UserId property on Habit
        // For now, we'll return all habits and filter based on the includeInactive parameter
        
        if (!includeInactive)
            query = query.Where(h => h.IsActive);

        return await query
            .OrderByDescending(h => h.CurrentStreak)
            .ThenBy(h => h.Name)
            .ToListAsync(cancellationToken);
    }

    public async Task AddAsync(Habit habit, CancellationToken cancellationToken = default)
    {
        await _context.Habits.AddAsync(habit, cancellationToken);
    }

    public async Task<int> SaveChangesAsync(CancellationToken cancellationToken = default)
    {
        return await _context.SaveChangesAsync(cancellationToken);
    }
}

public class GoalRepository : IGoalRepository
{
    private readonly HeyDavDbContext _context;

    public GoalRepository(HeyDavDbContext context)
    {
        _context = context;
    }

    public async Task<Goal?> GetByIdAsync(Guid id, CancellationToken cancellationToken = default)
    {
        return await _context.Goals
            .Include(g => g.Milestones)
            .FirstOrDefaultAsync(g => g.Id == id, cancellationToken);
    }

    public async Task<List<Goal>> GetByUserIdAsync(string userId, bool includeCompleted = false, CancellationToken cancellationToken = default)
    {
        var query = _context.Goals
            .Include(g => g.Milestones)
            .AsQueryable();

        // Note: In a real implementation, you'd have a UserId property on Goal
        // For now, we'll filter based on the includeCompleted parameter
        
        if (!includeCompleted)
            query = query.Where(g => g.Status != Domain.Goals.Entities.GoalStatus.Achieved && 
                                    g.Status != Domain.Goals.Entities.GoalStatus.Abandoned);

        return await query
            .OrderByDescending(g => g.Priority)
            .ThenByDescending(g => g.Progress)
            .ThenBy(g => g.TargetDate)
            .ToListAsync(cancellationToken);
    }

    public async Task AddAsync(Goal goal, CancellationToken cancellationToken = default)
    {
        await _context.Goals.AddAsync(goal, cancellationToken);
    }

    public async Task<int> SaveChangesAsync(CancellationToken cancellationToken = default)
    {
        return await _context.SaveChangesAsync(cancellationToken);
    }
}