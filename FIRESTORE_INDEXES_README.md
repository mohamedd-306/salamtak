# Firestore Indexes Configuration

## Overview
This document explains the Firestore indexes defined in `firestore.indexes.json` and when to deploy them.

## Current Indexes

### 1. Products Collection Index
- **Fields**: `category` (ASC) + `createdAt` (DESC)
- **Purpose**: Enables efficient querying of products by category with newest first
- **Used by**: Product listing screens

### 2. Reports Collection - National ID Index
- **Fields**: `nationalId` (ASC) + `createdAt` (DESC)
- **Purpose**: Enables efficient querying of user reports by national ID with newest first
- **Used by**: User history screen, admin dashboard
- **Note**: Currently optional as Task 3 removes orderBy to avoid index issues, but useful for future optimization

### 3. Reports Collection - UID Index
- **Fields**: `uid` (ASC) + `createdAt` (DESC)
- **Purpose**: Enables efficient querying of user reports by Firebase UID with newest first
- **Used by**: Fallback query when nationalId is not available
- **Note**: Currently optional as Task 3 removes orderBy to avoid index issues, but useful for future optimization

## When to Deploy Indexes

### Initial Setup
If this is a new Firebase project or you haven't deployed indexes yet:
```bash
firebase deploy --only firestore:indexes
```

### After Adding New Indexes
Whenever you add new composite indexes to `firestore.indexes.json`:
```bash
firebase deploy --only firestore:indexes
```

### Production Deployment
Before deploying code that uses these queries with `orderBy`:
1. Deploy indexes first: `firebase deploy --only firestore:indexes`
2. Wait for indexes to build (can take minutes to hours depending on data size)
3. Check index status in Firebase Console: Firestore → Indexes
4. Once indexes show "Enabled", deploy your application code

## Current Implementation Note

As of the bugfix implementation (Tasks 1-6), the application **does not require** these indexes to function because:
- Task 3 removed `orderBy('createdAt')` from queries to avoid index requirement errors
- Reports are sorted in memory after fetching from Firestore
- This ensures the app works immediately without waiting for index builds

However, these indexes are included for **future optimization**:
- If you have many reports (1000+), in-memory sorting becomes inefficient
- Re-enabling `orderBy` with these indexes will improve query performance
- Firestore will handle sorting server-side, reducing data transfer and client processing

## How to Re-enable Server-Side Sorting

Once indexes are deployed and enabled:

1. In `lib/services/database_service.dart`, modify `getUserReportsByNationalId()`:
```dart
// Change from:
.get()

// To:
.orderBy('createdAt', descending: true)
.get()
```

2. Remove the in-memory sorting code:
```dart
// Remove this:
reports.sort((a, b) {
  // ... sorting logic
});
```

3. Do the same for `getAllReportsStream()` and any other report queries

## Monitoring Index Status

Check index build status:
1. Go to Firebase Console
2. Navigate to Firestore Database → Indexes
3. Look for indexes with status:
   - **Building**: Index is being created (wait before deploying code)
   - **Enabled**: Index is ready to use
   - **Error**: Check error message and fix configuration

## Troubleshooting

### Index Build Failed
- Check that field names match exactly (case-sensitive)
- Verify collection name is correct
- Ensure you have data in the collection (empty collections can't build indexes)

### Query Still Slow After Index Deployment
- Verify index status is "Enabled" in Firebase Console
- Check that your query exactly matches the index fields and order
- Consider adding more specific indexes for your query patterns

### Index Not Being Used
- Firestore automatically selects the best index
- Use Firebase Console → Firestore → Usage tab to see which indexes are being used
- Query must match index fields exactly (same order, same ASC/DESC)

## Additional Resources
- [Firestore Index Documentation](https://firebase.google.com/docs/firestore/query-data/indexing)
- [Firebase CLI Reference](https://firebase.google.com/docs/cli)
