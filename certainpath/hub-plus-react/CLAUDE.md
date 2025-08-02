# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Commands

### Development
```bash
# Install dependencies
yarn install

# Start development server
yarn start

# Start with HTTPS (for Auth0 or other services requiring secure context)
yarn start-https

# Build for production
yarn build

# Run tests
yarn test

# Run tests in watch mode
yarn test --watch

# Run tests with coverage
yarn test --coverage
```

## Architecture Overview

This is a React 18 TypeScript application built with Create React App and enhanced with CRACO for custom webpack configuration. The application follows a modular, domain-driven architecture.

### Key Technologies
- **State Management**: Redux Toolkit with typed hooks (`app/hooks.ts`)
- **Authentication**: Auth0 with `AuthenticationGuard` component
- **API Layer**: Axios with interceptors (`api/axiosInstance.ts`) and Apollo Client for GraphQL
- **Routing**: React Router v6 with route modules in `app/routes/`
- **Styling**: Tailwind CSS with custom theme and shadcn/ui components
- **Forms**: React Hook Form with Zod validation schemas

### Module Structure
The application is organized into feature modules under `src/modules/`:
- **emailManagement**: Email campaigns and templates
- **eventRegistration**: Event management and registration
- **hub**: Core application features including dashboards, resource management, and user management
- **stochastic**: Campaign and mailing management
- **partnerNetwork**: Partner network features
- **scoreboard**: Scoreboard functionality

Each module typically contains:
- `features/`: Feature-specific components and logic
- `api/`: API endpoints and types
- `components/`: Feature-specific components
- `hooks/`: Custom hooks for the feature
- `slices/`: Redux slices for state management

### API Pattern
All API calls follow a consistent pattern:
1. API functions in `api/[feature]/[feature]Api.ts`
2. TypeScript types in `api/[feature]/types.ts`
3. Custom hooks that use React Query or Redux Toolkit Query
4. Error handling through `utils/extractErrorMessage.ts`

### Component Patterns
- **UI Components**: Located in `components/ui/` (shadcn/ui based)
- **Business Components**: Located in `components/` with PascalCase naming
- **Layout Components**: `MainLayout`, `SecondaryLayout` for consistent app structure
- **Guards**: `AuthenticationGuard` and `PermissionGuard` for access control

### State Management
- Global state uses Redux Toolkit with slices organized by feature
- Store configuration in `app/store.ts`
- Use typed hooks from `app/hooks.ts`: `useAppDispatch`, `useAppSelector`
- Async operations use createAsyncThunk or RTK Query

### Navigation
Navigation is configured in `src/navigation/` with separate configurations for different app sections:
- `unifiedNavigation.ts`: Main navigation structure
- Module-specific navigation files (e.g., `hubNavigation.ts`)
- Breadcrumb configuration in `breadcrumbConfig.ts`

### Testing
Tests use Jest and React Testing Library. Test files are colocated with components using `.test.tsx` extension.

### Build Configuration
- **CRACO**: Customizes webpack without ejecting (`craco.config.cjs`)
- **Path Aliases**: Use `@/` to import from `src/` directory
- **TypeScript**: Strict mode enabled with comprehensive type checking