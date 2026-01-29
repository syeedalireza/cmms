# ADR 005: Feature-Based Frontend Architecture

**Date**: 2026-01-28  
**Status**: Accepted  
**Decision Makers**: Architecture Team

## Context

We need to organize the React frontend code in a way that:
- Scales with the growing application
- Promotes code reusability
- Makes features easy to find and modify
- Reduces coupling between modules
- Supports parallel development

## Decision

We will use a **Feature-Based Architecture** (also known as Domain-Driven Frontend) instead of a traditional layered approach.

## Structure

```
src/
├── features/              # Feature modules
│   ├── assets/
│   │   ├── components/    # Feature-specific components
│   │   ├── hooks/         # Feature-specific hooks
│   │   ├── api/           # API client for this feature
│   │   ├── types/         # TypeScript types
│   │   ├── utils/         # Feature utilities
│   │   └── index.ts       # Public exports
│   ├── workorders/
│   ├── maintenance/
│   └── dashboard/
├── components/            # Shared UI components
│   ├── ui/               # shadcn/ui components
│   └── common/           # App-specific shared components
├── hooks/                # Shared hooks
├── lib/                  # Utilities and helpers
├── services/             # Shared services
├── stores/               # Global state (Zustand)
└── types/                # Shared TypeScript types
```

## Rationale

### Feature-Based vs Layer-Based

**Traditional Layer-Based** ❌
```
src/
├── components/    # ALL components
├── hooks/         # ALL hooks
├── services/      # ALL services
└── utils/         # ALL utilities
```

Problems:
- Hard to find related files
- Changes require touching many folders
- Unclear boundaries
- Everything imports everything

**Feature-Based** ✅
```
src/features/assets/
├── components/
│   ├── AssetList.tsx
│   ├── AssetForm.tsx
│   └── AssetDetails.tsx
├── hooks/
│   ├── useAssets.ts
│   └── useAssetMutations.ts
├── api/
│   └── assetsApi.ts
└── types/
    └── asset.types.ts
```

Benefits:
- Everything related in one place
- Clear feature boundaries
- Easy to find and modify
- Can develop features in parallel

## Feature Module Example

### assets/index.ts (Public API)
```typescript
// Only export what other features need
export { AssetList } from './components/AssetList';
export { useAssets } from './hooks/useAssets';
export type { Asset, AssetStatus } from './types/asset.types';

// Internal components NOT exported
```

### assets/api/assetsApi.ts
```typescript
import { apiClient } from '@/lib/api-client';
import type { Asset, CreateAssetDTO } from '../types/asset.types';

export const assetsApi = {
  getAll: () => apiClient.get<Asset[]>('/assets'),
  getById: (id: string) => apiClient.get<Asset>(`/assets/${id}`),
  create: (data: CreateAssetDTO) => apiClient.post<Asset>('/assets', data),
  update: (id: string, data: Partial<Asset>) => 
    apiClient.put<Asset>(`/assets/${id}`, data),
  delete: (id: string) => apiClient.delete(`/assets/${id}`),
};
```

### assets/hooks/useAssets.ts
```typescript
import { useQuery } from '@tanstack/react-query';
import { assetsApi } from '../api/assetsApi';

export function useAssets() {
  return useQuery({
    queryKey: ['assets'],
    queryFn: assetsApi.getAll,
  });
}

export function useAsset(id: string) {
  return useQuery({
    queryKey: ['assets', id],
    queryFn: () => assetsApi.getById(id),
    enabled: !!id,
  });
}
```

### assets/hooks/useAssetMutations.ts
```typescript
import { useMutation, useQueryClient } from '@tanstack/react-query';
import { assetsApi } from '../api/assetsApi';
import { toast } from '@/components/ui/use-toast';

export function useCreateAsset() {
  const queryClient = useQueryClient();
  
  return useMutation({
    mutationFn: assetsApi.create,
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['assets'] });
      toast({ title: 'Asset created successfully' });
    },
    onError: (error) => {
      toast({ title: 'Failed to create asset', variant: 'destructive' });
    },
  });
}
```

### assets/components/AssetList.tsx
```typescript
import { useAssets } from '../hooks/useAssets';
import { AssetCard } from './AssetCard';
import { Skeleton } from '@/components/ui/skeleton';

export function AssetList() {
  const { data: assets, isLoading } = useAssets();
  
  if (isLoading) {
    return <Skeleton className="h-64" />;
  }
  
  return (
    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
      {assets?.map(asset => (
        <AssetCard key={asset.id} asset={asset} />
      ))}
    </div>
  );
}
```

## Import Rules

### ✅ Allowed
```typescript
// Within feature
import { useAssets } from '../hooks/useAssets';

// From shared
import { Button } from '@/components/ui/button';
import { apiClient } from '@/lib/api-client';

// From other feature (via public API)
import { useWorkOrders } from '@/features/workorders';
```

### ❌ Not Allowed
```typescript
// Reaching into another feature's internals
import { WorkOrderCard } from '@/features/workorders/components/WorkOrderCard';
//                                                     ^^^^^^^^^^
// Should import from '@/features/workorders' instead
```

## Shared Code

### Shared Components (`src/components/`)
```
components/
├── ui/              # shadcn/ui primitives
│   ├── button.tsx
│   ├── dialog.tsx
│   └── ...
└── common/          # App-specific shared
    ├── Header.tsx
    ├── Sidebar.tsx
    └── PageLayout.tsx
```

### Shared Hooks (`src/hooks/`)
```typescript
// src/hooks/useDebounce.ts
export function useDebounce<T>(value: T, delay: number): T {
  // Generic utility hook
}
```

### Shared Types (`src/types/`)
```typescript
// src/types/api.types.ts
export interface PaginatedResponse<T> {
  data: T[];
  total: number;
  page: number;
}
```

## State Management

### Server State: TanStack Query (per feature)
```typescript
// In each feature
useQuery(['assets'], assetsApi.getAll);
```

### Client State: Zustand (global when needed)
```typescript
// src/stores/authStore.ts
export const useAuthStore = create<AuthState>((set) => ({
  user: null,
  setUser: (user) => set({ user }),
  logout: () => set({ user: null }),
}));
```

## Routing

```typescript
// src/App.tsx
import { AssetRoutes } from '@/features/assets';
import { WorkOrderRoutes } from '@/features/workorders';

function App() {
  return (
    <Routes>
      <Route path="/assets/*" element={<AssetRoutes />} />
      <Route path="/workorders/*" element={<WorkOrderRoutes />} />
    </Routes>
  );
}

// src/features/assets/routes.tsx
export function AssetRoutes() {
  return (
    <Routes>
      <Route index element={<AssetList />} />
      <Route path=":id" element={<AssetDetails />} />
      <Route path="new" element={<AssetForm />} />
    </Routes>
  );
}
```

## Consequences

### Positive
✅ **Scalability**: Easy to add new features  
✅ **Maintainability**: Related code together  
✅ **Testability**: Feature can be tested in isolation  
✅ **Parallel Development**: Teams work on different features  
✅ **Code Splitting**: Easy to lazy-load features  
✅ **Discoverability**: Know where to find code  

### Negative
❌ **Initial Setup**: More folders to create  
❌ **Potential Duplication**: Might duplicate some code  
❌ **Learning Curve**: Team needs to understand structure  

### Mitigation
- Create feature templates/generators
- Clear documentation and examples
- Code review for import violations
- Use ESLint rules to enforce boundaries

## Code Splitting

```typescript
// Lazy load features
const AssetsFeature = lazy(() => import('@/features/assets'));
const WorkOrdersFeature = lazy(() => import('@/features/workorders'));

<Suspense fallback={<LoadingSpinner />}>
  <Routes>
    <Route path="/assets/*" element={<AssetsFeature />} />
    <Route path="/workorders/*" element={<WorkOrdersFeature />} />
  </Routes>
</Suspense>
```

## Alternatives Considered

### 1. Layer-Based (Traditional)
**Rejected** because:
- Doesn't scale well
- Hard to navigate
- Tight coupling

### 2. Atomic Design
**Rejected** because:
- Too granular for business features
- Better for design systems

### 3. Module Federation (Micro-Frontends)
**Rejected** because:
- Too complex for our size
- Can adopt later if needed

## References

- [Feature-Sliced Design](https://feature-sliced.design/)
- [Domain-Driven Frontend](https://khalilstemmler.com/articles/client-side-architecture/introduction/)
- [React Folder Structure](https://www.robinwieruch.de/react-folder-structure/)
