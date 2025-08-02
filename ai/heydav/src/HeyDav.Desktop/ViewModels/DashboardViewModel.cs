using CommunityToolkit.Mvvm.ComponentModel;
using CommunityToolkit.Mvvm.Input;
using System.Collections.ObjectModel;
using HeyDav.Application.Common.Interfaces;
using HeyDav.Application.TodoManagement.Queries;
using HeyDav.Application.TodoManagement.Commands;
using HeyDav.Domain.TodoManagement.Entities;
using HeyDav.Domain.TodoManagement.Enums;
using HeyDav.Infrastructure.Services;

namespace HeyDav.Desktop.ViewModels;

public partial class DashboardViewModel : ViewModelBase
{
    private readonly IMediator _mediator;
    private readonly IVoiceCommandService _voiceCommandService;

    public DashboardViewModel(
        IMediator mediator, 
        IVoiceCommandService voiceCommandService)
    {
        _mediator = mediator ?? throw new ArgumentNullException(nameof(mediator));
        _voiceCommandService = voiceCommandService ?? throw new ArgumentNullException(nameof(voiceCommandService));
        
        TodoItems = new ObservableCollection<TodoItemViewModel>();
        TodaysTodos = new ObservableCollection<TodoItemViewModel>();
        
        LoadDataCommand = new AsyncRelayCommand(LoadDataAsync);
        AddTodoCommand = new AsyncRelayCommand(AddTodoAsync);
        ToggleVoiceActivationCommand = new AsyncRelayCommand(ToggleVoiceActivationAsync);
        
        // Set up voice service event handlers
        _voiceCommandService.CommandProcessed += OnCommandProcessed;
        
        // Load data initially
        _ = LoadDataAsync();
        
        // Start voice activation
        _ = StartVoiceActivationAsync();
    }

    [ObservableProperty]
    private string _dashboardTitle = "Hey-Dav Dashboard";

    [ObservableProperty]
    private DateTime _currentDate = DateTime.Today;

    [ObservableProperty]
    private string _newTodoTitle = string.Empty;

    [ObservableProperty]
    private bool _isLoading = false;

    [ObservableProperty]
    private int _completedTodosToday = 0;

    [ObservableProperty]
    private int _pendingTodosCount = 0;

    [ObservableProperty]
    private int _overdueTodosCount = 0;

    [ObservableProperty]
    private bool _isVoiceActivated = false;

    [ObservableProperty]
    private string _voiceStatus = "Voice Inactive";

    [ObservableProperty]
    private string _lastVoiceCommand = string.Empty;

    [ObservableProperty]
    private bool _isListeningForCommand = false;

    public ObservableCollection<TodoItemViewModel> TodoItems { get; }
    public ObservableCollection<TodoItemViewModel> TodaysTodos { get; }

    public IAsyncRelayCommand LoadDataCommand { get; }
    public IAsyncRelayCommand AddTodoCommand { get; }
    public IAsyncRelayCommand ToggleVoiceActivationCommand { get; }

    private async Task LoadDataAsync()
    {
        try
        {
            IsLoading = true;

            // Load all incomplete todos
            var allTodos = await _mediator.Send(new GetTodosQuery());
            
            // Load today's scheduled todos
            var todaysTodos = await _mediator.Send(new GetTodosByDateQuery(DateTime.Today));

            // Update collections on UI thread
            TodoItems.Clear();
            TodaysTodos.Clear();

            foreach (var todo in allTodos)
            {
                TodoItems.Add(new TodoItemViewModel(todo, _mediator));
            }

            foreach (var todo in todaysTodos)
            {
                TodaysTodos.Add(new TodoItemViewModel(todo, _mediator));
            }

            // Update statistics
            UpdateStatistics(allTodos);
        }
        catch (Exception ex)
        {
            // TODO: Handle error properly with notification service
            System.Diagnostics.Debug.WriteLine($"Error loading data: {ex.Message}");
        }
        finally
        {
            IsLoading = false;
        }
    }

    private void UpdateStatistics(IReadOnlyList<TodoItem> todos)
    {
        var today = DateTime.Today;
        
        CompletedTodosToday = todos.Count(t => 
            t.Status == TodoStatus.Completed && 
            t.CompletedDate?.Date == today);

        PendingTodosCount = todos.Count(t => 
            t.Status == TodoStatus.NotStarted || 
            t.Status == TodoStatus.InProgress);

        OverdueTodosCount = todos.Count(t => 
            t.DueDate < DateTime.Now && 
            t.Status != TodoStatus.Completed && 
            t.Status != TodoStatus.Cancelled);
    }

    private async Task AddTodoAsync()
    {
        if (string.IsNullOrWhiteSpace(NewTodoTitle))
            return;

        try
        {
            IsLoading = true;

            var command = new CreateTodoCommand(
                Title: NewTodoTitle.Trim(),
                Priority: Priority.Medium,
                DueDate: DateTime.Today.AddDays(1) // Default to tomorrow
            );

            await _mediator.Send(command);
            
            NewTodoTitle = string.Empty;

            // Reload data to show the new todo
            await LoadDataAsync();
        }
        catch (Exception ex)
        {
            System.Diagnostics.Debug.WriteLine($"Error adding todo: {ex.Message}");
        }
        finally
        {
            IsLoading = false;
        }
    }

    public async Task RefreshAsync()
    {
        await LoadDataAsync();
    }

    private async Task StartVoiceActivationAsync()
    {
        try
        {
            await _voiceCommandService.StartAsync();
            IsVoiceActivated = true;
            UpdateVoiceStatus();
        }
        catch (Exception ex)
        {
            VoiceStatus = "Voice Error";
            System.Diagnostics.Debug.WriteLine($"Error starting voice activation: {ex.Message}");
        }
    }

    private async Task ToggleVoiceActivationAsync()
    {
        try
        {
            if (IsVoiceActivated)
            {
                await _voiceCommandService.StopAsync();
                IsVoiceActivated = false;
            }
            else
            {
                await _voiceCommandService.StartAsync();
                IsVoiceActivated = true;
            }
            UpdateVoiceStatus();
        }
        catch (Exception ex)
        {
            System.Diagnostics.Debug.WriteLine($"Error toggling voice activation: {ex.Message}");
        }
    }

    private async void OnCommandProcessed(object? sender, VoiceCommandResult result)
    {
        try
        {
            LastVoiceCommand = result.Response;
            
            if (result.Success)
            {
                VoiceStatus = result.Response;
                
                // Handle specific actions
                switch (result.Action)
                {
                    case VoiceCommandAction.ShowTodoList:
                    case VoiceCommandAction.NavigateToTodos:
                    case VoiceCommandAction.CreateNewTodo:
                        await LoadDataAsync();
                        break;
                }
            }
            else
            {
                VoiceStatus = result.Response;
            }

            // Reset status after a delay
            _ = Task.Run(async () =>
            {
                await Task.Delay(3000);
                UpdateVoiceStatus();
            });
        }
        catch (Exception ex)
        {
            VoiceStatus = "Command error";
            System.Diagnostics.Debug.WriteLine($"Error processing voice command result: {ex.Message}");
        }
    }

    private void UpdateVoiceStatus()
    {
        if (!IsVoiceActivated)
        {
            VoiceStatus = "Voice Inactive";
        }
        else
        {
            VoiceStatus = _voiceCommandService.IsRunning ? "Voice Active - Say 'Hey Dav'" : "Voice Error";
        }
    }
}

public partial class TodoItemViewModel : ViewModelBase
{
    private readonly TodoItem _todoItem;
    private readonly IMediator _mediator;

    public TodoItemViewModel(TodoItem todoItem, IMediator mediator)
    {
        _todoItem = todoItem ?? throw new ArgumentNullException(nameof(todoItem));
        _mediator = mediator ?? throw new ArgumentNullException(nameof(mediator));

        ToggleCompleteCommand = new AsyncRelayCommand(ToggleCompleteAsync);
    }

    public Guid Id => _todoItem.Id;
    public string Title => _todoItem.Title;
    public string? Description => _todoItem.Description;
    public Priority Priority => _todoItem.Priority;
    public TodoStatus Status => _todoItem.Status;
    public DateTime? DueDate => _todoItem.DueDate;
    public DateTime? ScheduledDate => _todoItem.ScheduledDate;
    public bool IsCompleted => _todoItem.Status == TodoStatus.Completed;
    public bool IsOverdue => _todoItem.DueDate < DateTime.Now && !IsCompleted;

    public string PriorityText => Priority switch
    {
        Priority.Low => "Low",
        Priority.Medium => "Medium", 
        Priority.High => "High",
        Priority.Urgent => "Urgent",
        _ => "Medium"
    };

    public string StatusText => Status switch
    {
        TodoStatus.NotStarted => "Not Started",
        TodoStatus.InProgress => "In Progress",
        TodoStatus.Completed => "Completed",
        TodoStatus.Cancelled => "Cancelled",
        TodoStatus.Deferred => "Deferred",
        _ => "Unknown"
    };

    public string DueDateText => DueDate?.ToString("MMM dd, yyyy") ?? "No due date";

    public IAsyncRelayCommand ToggleCompleteCommand { get; }

    private async Task ToggleCompleteAsync()
    {
        try
        {
            if (IsCompleted)
            {
                // TODO: Implement uncomplete functionality
                // This would require adding the capability to the domain
            }
            else
            {
                _todoItem.Complete();
                // TODO: Save changes through a repository or command
                // await _mediator.Send(new UpdateTodoCommand(_todoItem));
            }

            OnPropertyChanged(nameof(IsCompleted));
            OnPropertyChanged(nameof(Status));
            OnPropertyChanged(nameof(StatusText));
        }
        catch (Exception ex)
        {
            System.Diagnostics.Debug.WriteLine($"Error toggling todo completion: {ex.Message}");
        }
    }
}