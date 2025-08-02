using CommunityToolkit.Mvvm.ComponentModel;
using HeyDav.Application.Common.Interfaces;
using HeyDav.Infrastructure.Services;
using HeyDav.Application.Voice;

namespace HeyDav.Desktop.ViewModels;

public partial class MainWindowViewModel : ViewModelBase
{
    public MainWindowViewModel(
        IMediator mediator,
        IVoiceActivationService voiceService,
        IVoiceCommandHandler voiceCommandHandler)
    {
        Dashboard = new DashboardViewModel(mediator, voiceService, voiceCommandHandler);
        CurrentViewModel = Dashboard;
    }

    [ObservableProperty]
    private ViewModelBase _currentViewModel;

    public DashboardViewModel Dashboard { get; }

    public string AppTitle { get; } = "Hey-Dav - Your Personal Productivity Assistant";
}
