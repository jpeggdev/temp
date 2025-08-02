using Microsoft.EntityFrameworkCore;
using HeyDav.Domain.Workflows.Entities;
using HeyDav.Application.Workflows.Interfaces;
using HeyDav.Application.Workflows.Models;
using HeyDav.Infrastructure.Persistence;

namespace HeyDav.Infrastructure.Productivity;

public class WorkflowTemplateRepository : IWorkflowTemplateRepository
{
    private readonly HeyDavDbContext _context;

    public WorkflowTemplateRepository(HeyDavDbContext context)
    {
        _context = context;
    }

    public async Task<WorkflowTemplate?> GetByIdAsync(Guid id, CancellationToken cancellationToken = default)
    {
        return await _context.WorkflowTemplates
            .Include(t => t.StepTemplates)
            .FirstOrDefaultAsync(t => t.Id == id, cancellationToken);
    }

    public async Task<List<WorkflowTemplate>> GetTemplatesAsync(WorkflowTemplateFilter? filter = null, CancellationToken cancellationToken = default)
    {
        var query = _context.WorkflowTemplates
            .Include(t => t.StepTemplates)
            .AsQueryable();

        if (filter != null)
        {
            if (filter.Category.HasValue)
                query = query.Where(t => t.Category == filter.Category.Value);

            if (filter.Difficulty.HasValue)
                query = query.Where(t => t.Difficulty == filter.Difficulty.Value);

            if (filter.IsActive.HasValue)
                query = query.Where(t => t.IsActive == filter.IsActive.Value);

            if (filter.IsBuiltIn.HasValue)
                query = query.Where(t => t.IsBuiltIn == filter.IsBuiltIn.Value);

            if (!string.IsNullOrEmpty(filter.CreatedBy))
                query = query.Where(t => t.CreatedBy == filter.CreatedBy);

            if (filter.Tags?.Any() == true)
                query = query.Where(t => filter.Tags.Any(tag => t.Tags.Contains(tag)));

            if (filter.MaxDuration.HasValue)
                query = query.Where(t => t.EstimatedDuration <= filter.MaxDuration.Value);

            if (filter.MinRating.HasValue)
                query = query.Where(t => t.Rating >= filter.MinRating.Value);

            if (!string.IsNullOrEmpty(filter.SearchText))
            {
                var searchText = filter.SearchText.ToLower();
                query = query.Where(t => 
                    t.Name.ToLower().Contains(searchText) || 
                    t.Description.ToLower().Contains(searchText) ||
                    t.Tags.Any(tag => tag.ToLower().Contains(searchText)));
            }
        }

        return await query
            .OrderByDescending(t => t.Rating)
            .ThenByDescending(t => t.UsageCount)
            .ThenBy(t => t.Name)
            .ToListAsync(cancellationToken);
    }

    public async Task<List<WorkflowTemplate>> GetActiveTemplatesAsync(CancellationToken cancellationToken = default)
    {
        return await _context.WorkflowTemplates
            .Include(t => t.StepTemplates)
            .Where(t => t.IsActive)
            .OrderByDescending(t => t.Rating)
            .ThenByDescending(t => t.UsageCount)
            .ToListAsync(cancellationToken);
    }

    public async Task AddAsync(WorkflowTemplate template, CancellationToken cancellationToken = default)
    {
        await _context.WorkflowTemplates.AddAsync(template, cancellationToken);
    }

    public async Task DeleteAsync(Guid id, CancellationToken cancellationToken = default)
    {
        var template = await _context.WorkflowTemplates.FindAsync(new object[] { id }, cancellationToken);
        if (template != null)
        {
            template.MarkAsDeleted();
        }
    }

    public async Task<int> SaveChangesAsync(CancellationToken cancellationToken = default)
    {
        return await _context.SaveChangesAsync(cancellationToken);
    }
}

public class WorkflowInstanceRepository : IWorkflowInstanceRepository
{
    private readonly HeyDavDbContext _context;

    public WorkflowInstanceRepository(HeyDavDbContext context)
    {
        _context = context;
    }

    public async Task<WorkflowInstance?> GetByIdAsync(Guid id, CancellationToken cancellationToken = default)
    {
        return await _context.WorkflowInstances
            .Include(i => i.StepInstances)
            .FirstOrDefaultAsync(i => i.Id == id, cancellationToken);
    }

    public async Task<List<WorkflowInstance>> GetByTemplateIdAsync(Guid templateId, DateTime? fromDate = null, DateTime? toDate = null, CancellationToken cancellationToken = default)
    {
        var query = _context.WorkflowInstances
            .Include(i => i.StepInstances)
            .Where(i => i.WorkflowTemplateId == templateId);

        if (fromDate.HasValue)
            query = query.Where(i => i.CreatedAt >= fromDate.Value);

        if (toDate.HasValue)
            query = query.Where(i => i.CreatedAt <= toDate.Value);

        return await query
            .OrderByDescending(i => i.CreatedAt)
            .ToListAsync(cancellationToken);
    }

    public async Task<List<WorkflowInstance>> GetActiveInstancesByTemplateIdAsync(Guid templateId, CancellationToken cancellationToken = default)
    {
        return await _context.WorkflowInstances
            .Include(i => i.StepInstances)
            .Where(i => i.WorkflowTemplateId == templateId && 
                       (i.Status == Domain.Workflows.Enums.WorkflowStatus.Running || 
                        i.Status == Domain.Workflows.Enums.WorkflowStatus.Paused))
            .ToListAsync(cancellationToken);
    }

    public async Task<List<WorkflowInstance>> GetByUserIdAsync(string userId, CancellationToken cancellationToken = default)
    {
        return await _context.WorkflowInstances
            .Include(i => i.StepInstances)
            .Where(i => i.UserId == userId)
            .OrderByDescending(i => i.CreatedAt)
            .ToListAsync(cancellationToken);
    }

    public async Task AddAsync(WorkflowInstance instance, CancellationToken cancellationToken = default)
    {
        await _context.WorkflowInstances.AddAsync(instance, cancellationToken);
    }

    public async Task<int> SaveChangesAsync(CancellationToken cancellationToken = default)
    {
        return await _context.SaveChangesAsync(cancellationToken);
    }
}