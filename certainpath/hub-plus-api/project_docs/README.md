# Project Documentation Structure - APM v{{VERSION}}

This directory contains all project-specific documentation generated and used by the APM Framework agents with **native sub-agent architecture** for 4-8x faster documentation generation.

## Directory Structure

### base/
Core project documents that serve as the foundation for all development work:
- `prd.md` - Product Requirements Document (created by PM agent)
- `architecture.md` - System Architecture Document (created by Architect agent)
- `frontend-architecture.md` - Frontend Architecture Document (created by Design Architect)
- `project_structure.md` - Project file/folder organization
- `development_workflow.md` - Development process and workflows
- `tech_stack.md` - Technology stack and dependencies
- `data-models.md` - Data structures and models
- `environment-vars.md` - Environment variable documentation

### epics/
Contains epic-level documentation:
- `epic-{n}.md` - Individual epic documents
- `epic-orchestration-guide.md` - Guide for managing epics

### stories/
User story documentation:
- `{epicNum}.{storyNum}.story.md` - Individual story files
- Stories are generated from epics by the SM agent

### qa/
Quality assurance documentation:
- `test-strategy.md` - Overall testing strategy
- `test-plan.md` - Detailed test plans
- `test-report.md` - Test execution reports
- `automation-plan.md` - Test automation plans
- `deployment-plan.md` - Deployment procedures
- `test-plans/` - Individual test plan documents
- `automation/` - Automation scripts and documentation

### index.md
Master index linking all documentation for easy navigation.

## Usage by Agents (v{{VERSION}} - Native Sub-Agent Enhanced)

- **Analyst**: Parallel research and analysis with native sub-agents
- **PM**: Concurrent PRD generation and epic management (4x speedup)
- **Architect**: `/parallel-architecture-review` for system-wide analysis
- **Design Architect**: Parallel component and UI/UX analysis
- **PO**: `/groom` with 18 native sub-agents for backlog optimization
- **SM**: `/parallel-sprint` coordinating 2-4 Developer sub-agents
- **Developer**: `/parallel-review` for concurrent code analysis
- **QA**: `/parallel-test` with AI/ML analytics (92% accuracy)

**Performance Impact**: Documentation generation now 4-8x faster with zero CLI crashes

