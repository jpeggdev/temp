using HeyDav.Domain.Workflows.Entities;
using HeyDav.Domain.Workflows.Enums;
using HeyDav.Domain.Workflows.ValueObjects;
using HeyDav.Application.Workflows.Interfaces;

namespace HeyDav.Application.Workflows.ProductivityWorkflows;

public static class DailyPlanningWorkflow
{
    public static WorkflowTemplate Create()
    {
        var template = WorkflowTemplate.Create(
            "Daily Planning Workflow",
            "A comprehensive daily planning workflow that helps you set priorities, schedule tasks, and optimize your day for maximum productivity.",
            WorkflowCategory.DailyPlanning,
            WorkflowDifficulty.Beginner,
            TimeSpan.FromMinutes(15),
            isBuiltIn: true);

        // Set auto-trigger for every weekday morning at 8 AM
        template.SetAutoTrigger(WorkflowTrigger.Scheduled("0 8 * * 1-5"));

        // Add configuration schema for customization
        template.SetConfigurationSchema(@"{
            ""properties"": {
                ""planningStartTime"": { ""type"": ""string"", ""format"": ""time"", ""default"": ""08:00"" },
                ""maxTasksPerDay"": { ""type"": ""integer"", ""minimum"": 3, ""maximum"": 20, ""default"": 8 },
                ""includeEnergyPlanning"": { ""type"": ""boolean"", ""default"": true },
                ""includeMoodCheck"": { ""type"": ""boolean"", ""default"": true },
                ""timeBlockingEnabled"": { ""type"": ""boolean"", ""default"": true }
            }
        }");

        // Step 1: Morning Energy and Mood Check
        template.AddStepTemplate(
            "Morning Energy & Mood Check",
            "Assess your current energy level and mood to inform your daily planning decisions.",
            WorkflowStepType.Input,
            1,
            true,
            @"{
                ""energyScale"": { ""min"": 1, ""max"": 10, ""default"": 5 },
                ""moodOptions"": [""Energetic"", ""Focused"", ""Neutral"", ""Tired"", ""Stressed"", ""Excited""],
                ""sleepQuality"": { ""scale"": ""1-5"", ""labels"": [""Poor"", ""Fair"", ""Good"", ""Very Good"", ""Excellent""] }
            }");

        // Step 2: Review Goals and Priorities
        template.AddStepTemplate(
            "Review Goals & Priorities",
            "Review your weekly and monthly goals to ensure today's tasks align with your bigger objectives.",
            WorkflowStepType.Review,
            2,
            true,
            @"{
                ""goalTypes"": [""Weekly"", ""Monthly"", ""Quarterly""],
                ""priorityMatrix"": ""Eisenhower"",
                ""showProgress"": true
            }");

        // Step 3: Review Yesterday's Performance
        template.AddStepTemplate(
            "Yesterday's Performance Review",
            "Quickly review what you accomplished yesterday and identify any carry-over tasks.",
            WorkflowStepType.Review,
            3,
            true,
            @"{
                ""showCompletedTasks"": true,
                ""showPendingTasks"": true,
                ""allowQuickReschedule"": true,
                ""captureWins"": true
            }");

        // Step 4: Identify Today's Top Priorities
        template.AddStepTemplate(
            "Identify Top 3 Priorities",
            "Select the 3 most important tasks that must be completed today. These should align with your goals.",
            WorkflowStepType.Decision,
            4,
            true,
            @"{
                ""maxPriorities"": 3,
                ""requireGoalAlignment"": true,
                ""estimateTime"": true,
                ""considerEnergyLevel"": true
            }");

        // Step 5: Schedule Deep Work Blocks
        template.AddStepTemplate(
            "Schedule Deep Work Blocks",
            "Block time for your most important and cognitively demanding tasks when your energy is highest.",
            WorkflowStepType.Action,
            5,
            true,
            @"{
                ""minBlockDuration"": ""45 minutes"",
                ""maxBlockDuration"": ""2 hours"",
                ""bufferTime"": ""15 minutes"",
                ""considerEnergyPeaks"": true
            }");

        // Step 6: Plan Routine Tasks
        template.AddStepTemplate(
            "Plan Routine & Administrative Tasks",
            "Schedule your routine tasks, emails, meetings, and administrative work around your deep work blocks.",
            WorkflowStepType.Action,
            6,
            true,
            @"{
                ""emailBlocks"": [""Morning"", ""Afternoon"", ""End of day""],
                ""meetingPreferences"": { ""maxBackToBack"": 2, ""bufferTime"": 15 },
                ""routineTasks"": [""Email"", ""Planning"", ""Review"", ""Communication""]
            }");

        // Step 7: Set Boundaries and Breaks
        template.AddStepTemplate(
            "Set Boundaries & Schedule Breaks",
            "Define when you'll be unavailable for interruptions and schedule necessary breaks to maintain energy.",
            WorkflowStepType.Action,
            7,
            true,
            @"{
                ""focusTimeBlocks"": true,
                ""breakFrequency"": ""Every 90 minutes"",
                ""lunchBreak"": { ""duration"": 60, ""flexible"": true },
                ""endOfDayTime"": ""18:00""
            }");

        // Step 8: Prepare Environment
        template.AddStepTemplate(
            "Prepare Work Environment",
            "Set up your physical and digital workspace for optimal productivity throughout the day.",
            WorkflowStepType.Action,
            8,
            false,
            @"{
                ""digitalCleanup"": [""Close unnecessary apps"", ""Organize desktop"", ""Check notifications""],
                ""physicalSetup"": [""Clear desk"", ""Prepare materials"", ""Check lighting""],
                ""tools"": [""Task manager"", ""Calendar"", ""Notes app"", ""Timer""]
            }");

        // Step 9: Set Daily Intention
        template.AddStepTemplate(
            "Set Daily Intention",
            "Define your intention for the day - how you want to approach your work and what success looks like.",
            WorkflowStepType.Input,
            9,
            false,
            @"{
                ""intentionPrompts"": [
                    ""What energy do I want to bring to my work today?"",
                    ""How will I measure success today?"",
                    ""What am I most excited about today?"",
                    ""How will I handle challenges that arise?""
                ],
                ""maxLength"": 200
            }");

        // Step 10: Final Schedule Review
        template.AddStepTemplate(
            "Final Schedule Review & Commitment",
            "Review your complete daily schedule and make any final adjustments before starting your day.",
            WorkflowStepType.Review,
            10,
            true,
            @"{
                ""showTimeBlocks"": true,
                ""validateCapacity"": true,
                ""allowLastMinuteChanges"": true,
                ""commitmentPrompt"": ""I commit to following this schedule and will reassess at lunch if needed.""
            }");

        // Add relevant tags
        template.AddTag("morning-routine");
        template.AddTag("planning");
        template.AddTag("productivity");
        template.AddTag("time-management");
        template.AddTag("goal-alignment");

        return template;
    }
}

public static class EmailManagementWorkflow
{
    public static WorkflowTemplate Create()
    {
        var template = WorkflowTemplate.Create(
            "Email Management Workflow",
            "An efficient email processing workflow that helps you achieve inbox zero while maintaining responsiveness and reducing email stress.",
            WorkflowCategory.EmailManagement,
            WorkflowDifficulty.Intermediate,
            TimeSpan.FromMinutes(20),
            isBuiltIn: true);

        // Set auto-trigger for scheduled email processing sessions
        template.SetAutoTrigger(WorkflowTrigger.Scheduled("0 9,14,17 * * 1-5"));

        // Configuration schema
        template.SetConfigurationSchema(@"{
            ""properties"": {
                ""maxProcessingTime"": { ""type"": ""integer"", ""minimum"": 10, ""maximum"": 60, ""default"": 20 },
                ""batchSize"": { ""type"": ""integer"", ""minimum"": 5, ""maximum"": 50, ""default"": 20 },
                ""enableAutoResponse"": { ""type"": ""boolean"", ""default"": false },
                ""priorityKeywords"": { ""type"": ""array"", ""items"": { ""type"": ""string"" } }
            }
        }");

        // Step 1: Set Processing Timer
        template.AddStepTemplate(
            "Set Email Processing Timer",
            "Set a timer to limit email processing time and maintain focus on other important tasks.",
            WorkflowStepType.Timer,
            1,
            true,
            @"{
                ""defaultDuration"": 20,
                ""allowExtension"": true,
                ""maxExtension"": 10,
                ""reminderInterval"": 5
            }");

        // Step 2: Quick Inbox Scan
        template.AddStepTemplate(
            "Quick Inbox Scan & Prioritization",
            "Quickly scan your inbox to identify urgent emails, spam, and emails that can be handled in 2 minutes or less.",
            WorkflowStepType.Review,
            2,
            true,
            @"{
                ""priorityIndicators"": [""From VIP"", ""Marked urgent"", ""Contains keywords"", ""Follow-up needed""],
                ""quickActions"": [""Delete"", ""Archive"", ""Mark as spam"", ""Quick reply""],
                ""maxScanTime"": 3
            }");

        // Step 3: Delete and Archive
        template.AddStepTemplate(
            "Delete Spam & Archive Newsletters",
            "Remove unnecessary emails to reduce inbox clutter and improve focus on important messages.",
            WorkflowStepType.Action,
            3,
            true,
            @"{
                ""autoCategories"": [""Newsletters"", ""Promotions"", ""Social updates"", ""Notifications""],
                ""deleteRules"": [""Obvious spam"", ""Outdated promotions"", ""Irrelevant notifications""],
                ""archiveRules"": [""FYI emails"", ""Completed tasks"", ""Reference materials""]
            }");

        // Step 4: Two-Minute Rule
        template.AddStepTemplate(
            "Apply Two-Minute Rule",
            "Handle emails that can be responded to or processed in 2 minutes or less immediately.",
            WorkflowStepType.Action,
            4,
            true,
            @"{
                ""timeLimit"": 2,
                ""quickActions"": [
                    ""Simple yes/no responses"",
                    ""Forwarding with brief note"",
                    ""Calendar invites"",
                    ""Simple information requests""
                ],
                ""templates"": true
            }");

        // Step 5: Categorize Remaining Emails
        template.AddStepTemplate(
            "Categorize & Label Remaining Emails",
            "Organize remaining emails by action required: respond, review, delegate, or schedule.",
            WorkflowStepType.Action,
            5,
            true,
            @"{
                ""categories"": [
                    { ""name"": ""Respond"", ""description"": ""Requires thoughtful response"", ""priority"": ""high"" },
                    { ""name"": ""Review"", ""description"": ""Needs careful reading/analysis"", ""priority"": ""medium"" },
                    { ""name"": ""Delegate"", ""description"": ""Can be handled by someone else"", ""priority"": ""low"" },
                    { ""name"": ""Schedule"", ""description"": ""Action needed but not urgent"", ""priority"": ""low"" }
                ],
                ""autoSuggestions"": true
            }");

        // Step 6: Schedule Response Time
        template.AddStepTemplate(
            "Schedule Response & Action Time",
            "Block time in your calendar for responding to emails that require more than 2 minutes.",
            WorkflowStepType.Action,
            6,
            true,
            @"{
                ""responseTimeBlocks"": [""30 minutes"", ""45 minutes"", ""60 minutes""],
                ""scheduleWithin"": ""24 hours"",
                ""prioritizeByImportance"": true,
                ""considerEnergyLevels"": true
            }");

        // Step 7: Set Up Follow-ups
        template.AddStepTemplate(
            "Set Up Follow-up Reminders",
            "Create reminders for emails that require follow-up or have pending responses from others.",
            WorkflowStepType.Action,
            7,
            false,
            @"{
                ""reminderIntervals"": [""2 days"", ""1 week"", ""2 weeks""],
                ""autoSnooze"": true,
                ""followUpTemplates"": true,
                ""trackResponses"": true
            }");

        // Step 8: Update Email Filters
        template.AddStepTemplate(
            "Update Email Filters & Rules",
            "Improve future email processing by updating filters and rules based on patterns you noticed today.",
            WorkflowStepType.Action,
            8,
            false,
            @"{
                ""suggestFilters"": true,
                ""commonPatterns"": [""Recurring senders"", ""Subject patterns"", ""CC/BCC patterns""],
                ""autoArchiveRules"": true,
                ""priorityRules"": true
            }");

        template.AddTag("email");
        template.AddTag("communication");
        template.AddTag("productivity");
        template.AddTag("inbox-zero");
        template.AddTag("time-management");

        return template;
    }
}

public static class MeetingPreparationWorkflow
{
    public static WorkflowTemplate Create()
    {
        var template = WorkflowTemplate.Create(
            "Meeting Preparation Workflow",
            "A thorough meeting preparation workflow that ensures you're well-prepared, meetings are productive, and follow-up actions are clear.",
            WorkflowCategory.MeetingPreparation,
            WorkflowDifficulty.Intermediate,
            TimeSpan.FromMinutes(12),
            isBuiltIn: true);

        // Auto-trigger 24 hours before scheduled meetings
        template.SetAutoTrigger(WorkflowTrigger.Event("meeting.scheduled", new Dictionary<string, object> { { "triggerBefore", "24 hours" } }));

        template.SetConfigurationSchema(@"{
            ""properties"": {
                ""preparationTime"": { ""type"": ""integer"", ""minimum"": 5, ""maximum"": 30, ""default"": 12 },
                ""includeAgendaCreation"": { ""type"": ""boolean"", ""default"": true },
                ""sendAgendaInAdvance"": { ""type"": ""boolean"", ""default"": true },
                ""meetingTypes"": { ""type"": ""array"", ""items"": { ""type"": ""string"" } }
            }
        }");

        // Step 1: Review Meeting Details
        template.AddStepTemplate(
            "Review Meeting Details & Context",
            "Understand the meeting purpose, attendees, and your role to prepare appropriately.",
            WorkflowStepType.Review,
            1,
            true,
            @"{
                ""reviewItems"": [
                    ""Meeting purpose and objectives"",
                    ""Attendee list and roles"",
                    ""Your expected contribution"",
                    ""Previous meeting outcomes"",
                    ""Related projects or decisions""
                ],
                ""contextSources"": [""Calendar"", ""Email threads"", ""Project documents"", ""Previous notes""]
            }");

        // Step 2: Define Success Criteria
        template.AddStepTemplate(
            "Define Meeting Success Criteria",
            "Clarify what a successful meeting outcome looks like from your perspective.",
            WorkflowStepType.Decision,
            2,
            true,
            @"{
                ""successCriteria"": [
                    ""Specific decisions made"",
                    ""Clear action items assigned"",
                    ""Questions answered"",
                    ""Information shared"",
                    ""Relationships built""
                ],
                ""yourGoals"": [""Learn"", ""Influence"", ""Update"", ""Decide"", ""Collaborate""]
            }");

        // Step 3: Prepare Content and Materials
        template.AddStepTemplate(
            "Prepare Content & Materials",
            "Gather all necessary documents, data, and materials you'll need during the meeting.",
            WorkflowStepType.Action,
            3,
            true,
            @"{
                ""materials"": [
                    ""Relevant documents"",
                    ""Data and reports"",
                    ""Presentation slides"",
                    ""Reference materials"",
                    ""Previous meeting notes""
                ],
                ""preparation"": [""Review key points"", ""Prepare talking points"", ""Anticipate questions""]
            }");

        // Step 4: Create or Review Agenda
        template.AddStepTemplate(
            "Create or Review Agenda",
            "Ensure there's a clear agenda that will guide the meeting toward productive outcomes.",
            WorkflowStepType.Action,
            4,
            true,
            @"{
                ""agendaItems"": [
                    ""Welcome and introductions"",
                    ""Review objectives"",
                    ""Main discussion topics"",
                    ""Decision points"",
                    ""Next steps and actions""
                ],
                ""timeAllocation"": true,
                ""prioritizeItems"": true,
                ""shareInAdvance"": true
            }");

        // Step 5: Prepare Questions
        template.AddStepTemplate(
            "Prepare Strategic Questions",
            "Develop thoughtful questions that will drive productive discussion and uncover important insights.",
            WorkflowStepType.Action,
            5,
            true,
            @"{
                ""questionTypes"": [
                    ""Clarifying questions"",
                    ""Strategic questions"",
                    ""Follow-up questions"",
                    ""Decision-forcing questions""
                ],
                ""techniques"": [""Open-ended"", ""Specific"", ""Hypothetical"", ""Priority-based""]
            }");

        // Step 6: Plan Participation Strategy
        template.AddStepTemplate(
            "Plan Your Participation Strategy",
            "Think about how you'll contribute to the meeting and when to speak up or listen.",
            WorkflowStepType.Decision,
            6,
            false,
            @"{
                ""strategies"": [
                    ""Lead discussion on expertise areas"",
                    ""Ask clarifying questions"",
                    ""Summarize key points"",
                    ""Bridge different viewpoints"",
                    ""Keep discussion on track""
                ],
                ""considerPersonalities"": true,
                ""timing"": [""Early contribution"", ""Strategic intervention"", ""Summary at end""]
            }");

        // Step 7: Technical Setup Check
        template.AddStepTemplate(
            "Technical Setup & Environment Check",
            "Ensure your technology and environment are ready for the meeting format (in-person or virtual).",
            WorkflowStepType.Action,
            7,
            true,
            @"{
                ""virtualMeeting"": [
                    ""Test camera and microphone"",
                    ""Check internet connection"",
                    ""Prepare backup options"",
                    ""Set up proper lighting"",
                    ""Choose quiet location""
                ],
                ""inPerson"": [
                    ""Confirm location and directions"",
                    ""Prepare physical materials"",
                    ""Plan arrival time"",
                    ""Bring necessary supplies""
                ]
            }");

        template.AddTag("meetings");
        template.AddTag("preparation");
        template.AddTag("productivity");
        template.AddTag("collaboration");
        template.AddTag("communication");

        return template;
    }
}