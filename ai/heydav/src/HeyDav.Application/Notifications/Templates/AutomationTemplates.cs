using HeyDav.Domain.Automation.Entities;
using HeyDav.Domain.Automation.Enums;
using HeyDav.Domain.Automation.ValueObjects;
using HeyDav.Domain.Notifications.Enums;

namespace HeyDav.Application.Notifications.Templates;

public static class AutomationTemplates
{
    public static class DailyStandupReminder
    {
        public static AutomationRule Create(TimeOnly standupTime, List<DayOfWeek> workingDays, string teamChannel)
        {
            var rule = new AutomationRule(
                "Daily Standup Reminder",
                "Automatically sends standup reminders and prepares agenda based on yesterday's work and today's priorities",
                category: "Team Management");

            // Schedule trigger for standup time
            var schedule = AutomationSchedule.CreateWeeklySchedule(workingDays, standupTime);
            rule.UpdateSchedule(schedule);

            // Time-based trigger
            var trigger = AutomationTrigger.CreateTimeTrigger(
                "Standup Time",
                DateTime.Today.Add(standupTime.ToTimeSpan()),
                "Trigger standup reminder at scheduled time");
            rule.AddTrigger(trigger);

            // Condition: Only during working days
            var workingDayCondition = AutomationCondition.CreateCustomCondition(
                "Working Day Check",
                "dayOfWeek",
                workingDays);
            rule.AddCondition(workingDayCondition);

            // Action 1: Generate standup agenda
            var generateAgendaAction = AutomationAction.CreateCustomAction(
                "Generate Standup Agenda",
                "Generate agenda based on recent tasks and calendar events",
                new Dictionary<string, object>
                {
                    ["includeCompletedTasks"] = true,
                    ["includeUpcomingDeadlines"] = true,
                    ["includeMeetings"] = true,
                    ["lookbackDays"] = 1,
                    ["lookaheadDays"] = 1
                });
            rule.AddAction(generateAgendaAction);

            // Action 2: Send notification to team
            var notificationAction = AutomationAction.CreateNotificationAction(
                "Daily Standup - Ready to Start!",
                "Your standup agenda is ready. Please review your updates and join the meeting.",
                teamChannel);
            rule.AddAction(notificationAction);

            // Configuration
            var config = new AutomationConfiguration()
                .WithPriority(AutomationPriority.Normal)
                .WithNotifications(false, true)
                .WithLogging(true, AutomationLogLevel.Information);
            rule.UpdateConfiguration(config);

            return rule;
        }
    }

    public static class MeetingFollowUp
    {
        public static AutomationRule Create(string calendarSource, TimeSpan followUpDelay)
        {
            var rule = new AutomationRule(
                "Meeting Follow-up Automation",
                "Automatically creates follow-up tasks and sends summaries after meetings end",
                category: "Meeting Management");

            // Event trigger for meeting end
            var trigger = AutomationTrigger.CreateEventTrigger(
                "Meeting Ended",
                "calendar.meeting.ended",
                "Triggered when a calendar meeting ends");
            trigger = trigger.WithConfiguration("source", calendarSource);
            rule.AddTrigger(trigger);

            // Condition: Meeting duration > 15 minutes
            var durationCondition = AutomationCondition.CreateCustomCondition(
                "Minimum Duration",
                "meeting.duration",
                TimeSpan.FromMinutes(15));
            rule.AddCondition(durationCondition);

            // Action 1: Wait for follow-up delay
            var delayAction = AutomationAction.CreateCustomAction(
                "Wait for Follow-up",
                "Wait before sending follow-up to allow meeting to fully end",
                new Dictionary<string, object>
                {
                    ["delay"] = followUpDelay
                });
            rule.AddAction(delayAction);

            // Action 2: Analyze meeting for action items
            var analyzeAction = AutomationAction.CreateCustomAction(
                "Extract Action Items",
                "Analyze meeting transcript/notes for action items and decisions",
                new Dictionary<string, object>
                {
                    ["useAI"] = true,
                    ["extractTasks"] = true,
                    ["extractDecisions"] = true,
                    ["extractNextSteps"] = true
                });
            rule.AddAction(analyzeAction);

            // Action 3: Create tasks for action items
            var createTasksAction = AutomationAction.CreateCustomAction(
                "Create Follow-up Tasks",
                "Create tasks for each identified action item",
                new Dictionary<string, object>
                {
                    ["assignToAttendees"] = true,
                    ["setDueDates"] = true,
                    ["linkToMeeting"] = true
                });
            rule.AddAction(createTasksAction);

            // Action 4: Send summary to attendees
            var summaryAction = AutomationAction.CreateEmailAction(
                "{meeting.attendees}",
                "Meeting Summary: {meeting.title}",
                "Please find the meeting summary and action items attached.");
            rule.AddAction(summaryAction);

            return rule;
        }
    }

    public static class DeadlineApproachAlert
    {
        public static AutomationRule Create(TimeSpan[] alertIntervals, NotificationPriority escalationPriority)
        {
            var rule = new AutomationRule(
                "Task Deadline Approach Alerts",
                "Sends escalating alerts as task deadlines approach with smart prioritization",
                category: "Task Management");

            // Multiple time-based triggers for different intervals
            foreach (var interval in alertIntervals)
            {
                var trigger = AutomationTrigger.CreateTimeTrigger(
                    $"Deadline Alert - {interval.TotalHours}h Before",
                    DateTime.UtcNow.Add(interval), // This would be dynamically calculated
                    $"Trigger alert {interval.TotalHours} hours before deadline");
                trigger = trigger.WithConfiguration("interval", interval);
                rule.AddTrigger(trigger);
            }

            // Condition: Task is not completed
            var notCompletedCondition = AutomationCondition.CreateEqualsCondition(
                "Task Not Completed",
                "task.status",
                "Pending");
            rule.AddCondition(notCompletedCondition);

            // Condition: High priority tasks get earlier alerts
            var priorityCondition = AutomationCondition.CreateCustomCondition(
                "Priority-based Alert",
                "task.priority",
                "High");
            rule.AddCondition(priorityCondition);

            // Action 1: Assess task completion likelihood
            var assessmentAction = AutomationAction.CreateCustomAction(
                "Assess Completion Risk",
                "Use AI to assess likelihood of on-time completion",
                new Dictionary<string, object>
                {
                    ["analyzeProgress"] = true,
                    ["checkDependencies"] = true,
                    ["evaluateWorkload"] = true,
                    ["considerHistory"] = true
                });
            rule.AddAction(assessmentAction);

            // Action 2: Send graduated notification
            var notificationAction = AutomationAction.CreateNotificationAction(
                "Task Deadline Approaching: {task.title}",
                "Your task '{task.title}' is due {task.timeUntilDeadline}. Completion risk: {assessment.risk}");
            rule.AddAction(notificationAction);

            // Action 3: Escalate if high risk
            var escalationAction = AutomationAction.CreateCustomAction(
                "Escalate High-Risk Tasks",
                "Escalate to manager/team if completion risk is high",
                new Dictionary<string, object>
                {
                    ["riskThreshold"] = 0.7,
                    ["escalateTo"] = "manager",
                    ["includeSuggestions"] = true
                });
            escalationAction = escalationAction.WithCondition("assessment.risk", ">", 0.7);
            rule.AddAction(escalationAction);

            var config = new AutomationConfiguration()
                .WithPriority(AutomationPriority.High)
                .WithNotifications(true, true)
                .WithRunMode(AutomationRunMode.Sequential);
            rule.UpdateConfiguration(config);

            return rule;
        }
    }

    public static class TaskDelegationWorkflow
    {
        public static AutomationRule Create(List<string> eligibleAssignees, string teamChannel)
        {
            var rule = new AutomationRule(
                "Smart Task Delegation",
                "Automatically delegates tasks based on workload, skills, and availability",
                category: "Task Management");

            // Manual trigger for delegation
            var manualTrigger = AutomationTrigger.CreateManualTrigger(
                "Delegate Task",
                "Manually triggered when a task needs delegation");
            rule.AddTrigger(manualTrigger);

            // Event trigger for overloaded user
            var overloadTrigger = AutomationTrigger.CreateEventTrigger(
                "User Overloaded",
                "user.workload.exceeded",
                "Triggered when user's workload exceeds threshold");
            rule.AddTrigger(overloadTrigger);

            // Condition: Task is delegatable
            var delegatableCondition = AutomationCondition.CreateEqualsCondition(
                "Task Delegatable",
                "task.canDelegate",
                true);
            rule.AddCondition(delegatableCondition);

            // Action 1: Analyze team availability and skills
            var analyzeTeamAction = AutomationAction.CreateCustomAction(
                "Analyze Team Capacity",
                "Analyze team member workloads, skills, and availability",
                new Dictionary<string, object>
                {
                    ["eligibleAssignees"] = eligibleAssignees,
                    ["considerSkills"] = true,
                    ["considerWorkload"] = true,
                    ["considerAvailability"] = true,
                    ["skillMatchWeight"] = 0.4,
                    ["workloadWeight"] = 0.4,
                    ["availabilityWeight"] = 0.2
                });
            rule.AddAction(analyzeTeamAction);

            // Action 2: Select best assignee
            var selectAssigneeAction = AutomationAction.CreateCustomAction(
                "Select Optimal Assignee",
                "Select the best team member for the task",
                new Dictionary<string, object>
                {
                    ["algorithm"] = "weighted_scoring",
                    ["requireConfirmation"] = true,
                    ["fallbackToManager"] = true
                });
            rule.AddAction(selectAssigneeAction);

            // Action 3: Update task assignment
            var updateTaskAction = AutomationAction.CreateCustomAction(
                "Update Task Assignment",
                "Assign task to selected team member",
                new Dictionary<string, object>
                {
                    ["updateAssignee"] = true,
                    ["addDelegationNote"] = true,
                    ["notifyPreviousAssignee"] = true
                });
            rule.AddAction(updateTaskAction);

            // Action 4: Send delegation notification
            var notificationAction = AutomationAction.CreateNotificationAction(
                "Task Delegated: {task.title}",
                "You have been assigned a new task: '{task.title}'. Priority: {task.priority}. Due: {task.dueDate}");
            rule.AddAction(notificationAction);

            // Action 5: Track delegation metrics
            var metricsAction = AutomationAction.CreateCustomAction(
                "Track Delegation Metrics",
                "Record delegation for workload balancing analytics",
                new Dictionary<string, object>
                {
                    ["trackSuccess"] = true,
                    ["updateWorkloadModel"] = true,
                    ["improveDelegationAlgorithm"] = true
                });
            rule.AddAction(metricsAction);

            return rule;
        }
    }

    public static class WeeklyReportGeneration
    {
        public static AutomationRule Create(DayOfWeek reportDay, TimeOnly reportTime, List<string> recipients)
        {
            var rule = new AutomationRule(
                "Weekly Productivity Report",
                "Generates and distributes weekly productivity reports with insights and recommendations",
                category: "Reporting");

            // Weekly schedule trigger
            var schedule = AutomationSchedule.CreateWeeklySchedule(
                new List<DayOfWeek> { reportDay },
                reportTime);
            rule.UpdateSchedule(schedule);

            var trigger = AutomationTrigger.CreateTimeTrigger(
                "Weekly Report Time",
                DateTime.Today.AddDays((int)reportDay - (int)DateTime.Today.DayOfWeek).Add(reportTime.ToTimeSpan()),
                "Generate weekly report");
            rule.AddTrigger(trigger);

            // Action 1: Gather productivity data
            var gatherDataAction = AutomationAction.CreateCustomAction(
                "Gather Weekly Data",
                "Collect productivity metrics for the past week",
                new Dictionary<string, object>
                {
                    ["includeTaskCompletion"] = true,
                    ["includeGoalProgress"] = true,
                    ["includeTimeTracking"] = true,
                    ["includeMoodData"] = true,
                    ["includeHabitTracking"] = true,
                    ["weekOffset"] = -1 // Previous week
                });
            rule.AddAction(gatherDataAction);

            // Action 2: Analyze trends and patterns
            var analyzeAction = AutomationAction.CreateCustomAction(
                "Analyze Productivity Trends",
                "Use AI to identify patterns and generate insights",
                new Dictionary<string, object>
                {
                    ["identifyTrends"] = true,
                    ["findBlockers"] = true,
                    ["generateRecommendations"] = true,
                    ["compareToGoals"] = true,
                    ["predictNextWeek"] = true
                });
            rule.AddAction(analyzeAction);

            // Action 3: Generate report
            var generateReportAction = AutomationAction.CreateCustomAction(
                "Generate Report",
                "Create formatted weekly productivity report",
                new Dictionary<string, object>
                {
                    ["format"] = "html",
                    ["includeCharts"] = true,
                    ["includeActionItems"] = true,
                    ["includeGoalAdjustments"] = true,
                    ["template"] = "weekly_productivity_report"
                });
            rule.AddAction(generateReportAction);

            // Action 4: Distribute report
            var distributeAction = AutomationAction.CreateEmailAction(
                string.Join(",", recipients),
                "Weekly Productivity Report - Week of {report.weekStartDate}",
                "{report.content}");
            rule.AddAction(distributeAction);

            // Action 5: Schedule follow-up actions
            var followUpAction = AutomationAction.CreateCustomAction(
                "Schedule Follow-up Actions",
                "Create tasks for recommended improvements",
                new Dictionary<string, object>
                {
                    ["createImprovementTasks"] = true,
                    ["scheduleGoalReview"] = true,
                    ["setReminders"] = true
                });
            rule.AddAction(followUpAction);

            var config = new AutomationConfiguration()
                .WithPriority(AutomationPriority.Normal)
                .WithTimeout(TimeSpan.FromMinutes(10))
                .WithNotifications(false, true);
            rule.UpdateConfiguration(config);

            return rule;
        }
    }

    public static class GoalProgressAlert
    {
        public static AutomationRule Create(double[] alertThresholds, TimeSpan checkInterval)
        {
            var rule = new AutomationRule(
                "Goal Progress Monitoring",
                "Monitors goal progress and sends alerts with course correction suggestions",
                category: "Goal Management");

            // Interval-based trigger
            var schedule = AutomationSchedule.CreateIntervalSchedule(checkInterval);
            rule.UpdateSchedule(schedule);

            var trigger = AutomationTrigger.CreateTimeTrigger(
                "Progress Check",
                DateTime.UtcNow.Add(checkInterval),
                "Check goal progress at regular intervals");
            rule.AddTrigger(trigger);

            // Action 1: Calculate progress for all active goals
            var calculateProgressAction = AutomationAction.CreateCustomAction(
                "Calculate Goal Progress",
                "Calculate current progress for all active goals",
                new Dictionary<string, object>
                {
                    ["includeSubgoals"] = true,
                    ["calculateVelocity"] = true,
                    ["predictCompletion"] = true,
                    ["identifyAtRisk"] = true
                });
            rule.AddAction(calculateProgressAction);

            // Action 2: Check against thresholds
            var checkThresholdsAction = AutomationAction.CreateCustomAction(
                "Check Alert Thresholds",
                "Check if any goals have crossed alert thresholds",
                new Dictionary<string, object>
                {
                    ["thresholds"] = alertThresholds,
                    ["checkUnderperforming"] = true,
                    ["checkOverdue"] = true,
                    ["checkOnTrack"] = true
                });
            rule.AddAction(checkThresholdsAction);

            // Action 3: Generate course correction suggestions
            var suggestionsAction = AutomationAction.CreateCustomAction(
                "Generate Suggestions",
                "Use AI to generate course correction suggestions",
                new Dictionary<string, object>
                {
                    ["analyzeBlockers"] = true,
                    ["suggestAdjustments"] = true,
                    ["recommendResources"] = true,
                    ["proposeMilestones"] = true
                });
            rule.AddAction(suggestionsAction);

            // Action 4: Send progress alert
            var alertAction = AutomationAction.CreateNotificationAction(
                "Goal Progress Alert: {goal.title}",
                "Goal '{goal.title}' is {goal.progressPercentage}% complete. {suggestions.summary}");
            rule.AddAction(alertAction);

            // Action 5: Create adjustment tasks if needed
            var adjustmentAction = AutomationAction.CreateCustomAction(
                "Create Adjustment Tasks",
                "Create tasks for suggested course corrections",
                new Dictionary<string, object>
                {
                    ["onlyForAtRiskGoals"] = true,
                    ["prioritizeByImpact"] = true,
                    ["scheduleReviews"] = true
                });
            rule.AddAction(adjustmentAction);

            return rule;
        }
    }

    public static class HabitReminderSystem
    {
        public static AutomationRule Create(string habitName, List<TimeOnly> reminderTimes, int adaptiveDelayMinutes)
        {
            var rule = new AutomationRule(
                $"Adaptive Habit Reminder - {habitName}",
                $"Smart reminder system for {habitName} habit with adaptive timing based on user behavior",
                category: "Habit Tracking");

            // Multiple time-based triggers for reminder times
            foreach (var time in reminderTimes)
            {
                var trigger = AutomationTrigger.CreateTimeTrigger(
                    $"Habit Reminder - {time}",
                    DateTime.Today.Add(time.ToTimeSpan()),
                    $"Reminder for {habitName} at {time}");
                trigger = trigger.WithConfiguration("habitName", habitName);
                rule.AddTrigger(trigger);
            }

            // Condition: Habit not completed today
            var notCompletedCondition = AutomationCondition.CreateEqualsCondition(
                "Habit Not Completed Today",
                $"habit.{habitName}.completedToday",
                false);
            rule.AddCondition(notCompletedCondition);

            // Action 1: Check user activity and mood
            var checkContextAction = AutomationAction.CreateCustomAction(
                "Check User Context",
                "Analyze user's current activity and mood for optimal timing",
                new Dictionary<string, object>
                {
                    ["checkActivityLevel"] = true,
                    ["checkMood"] = true,
                    ["checkLocation"] = true,
                    ["checkCalendar"] = true
                });
            rule.AddAction(checkContextAction);

            // Action 2: Adaptive timing decision
            var timingAction = AutomationAction.CreateCustomAction(
                "Optimize Reminder Timing",
                "Decide whether to send reminder now or delay based on context",
                new Dictionary<string, object>
                {
                    ["maxDelay"] = adaptiveDelayMinutes,
                    ["considerMood"] = true,
                    ["avoidBusyTimes"] = true,
                    ["learnFromHistory"] = true
                });
            rule.AddAction(timingAction);

            // Action 3: Send personalized reminder
            var reminderAction = AutomationAction.CreateNotificationAction(
                $"Time for {habitName}! 💪",
                "You've got this! Your {habitName} streak is {habit.currentStreak} days. " +
                "Perfect timing based on your energy level: {context.energyLevel}");
            rule.AddAction(reminderAction);

            // Action 4: Schedule follow-up if not completed
            var followUpAction = AutomationAction.CreateCustomAction(
                "Schedule Follow-up",
                "Schedule a gentle follow-up reminder if habit isn't completed",
                new Dictionary<string, object>
                {
                    ["followUpDelay"] = TimeSpan.FromHours(2),
                    ["maxFollowUps"] = 2,
                    ["adjustTone"] = "encouraging"
                });
            rule.AddAction(followUpAction);

            // Action 5: Update machine learning model with response
            var learningAction = AutomationAction.CreateCustomAction(
                "Update Learning Model",
                "Record reminder effectiveness for future optimization",
                new Dictionary<string, object>
                {
                    ["trackResponse"] = true,
                    ["updateOptimalTimes"] = true,
                    ["improvePersonalization"] = true
                });
            rule.AddAction(learningAction);

            var config = new AutomationConfiguration()
                .WithPriority(AutomationPriority.Normal)
                .WithRunMode(AutomationRunMode.Sequential)
                .WithNotifications(false, true);
            rule.UpdateConfiguration(config);

            return rule;
        }
    }
}

// Extension method to make condition creation easier
public static class AutomationConditionExtensions
{
    public static AutomationCondition CreateCustomCondition(this AutomationCondition condition, string name, string field, object value)
    {
        return new AutomationCondition(
            AutomationConditionType.Custom,
            name,
            field,
            value,
            description: $"Custom condition for {name}");
    }
}

// Extension method to make action conditions easier
public static class AutomationActionExtensions
{
    public static AutomationAction WithCondition(this AutomationAction action, string field, string op, object value)
    {
        return action.WithConfiguration($"condition.{field}.{op}", value);
    }
}