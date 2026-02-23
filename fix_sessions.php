<?php
/**
 * Script to fix the MongoDB sessions collection.
 * 
 * The issue: A unique index `id_1` exists on the `sessions` collection.
 * When Laravel's DatabaseSessionHandler inserts sessions, it uses an `id` field
 * (not MongoDB's `_id`). If any document has `id: null`, subsequent inserts
 * with null `id` will fail with a duplicate key error.
 *
 * Fix: Drop the `id_1` index, clear corrupted sessions, and let Laravel
 * recreate them properly.
 */

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

$mongodb = DB::connection('mongodb')->getMongoDB();
$collection = $mongodb->selectCollection('sessions');

// 1. List current indexes
echo "=== Current Indexes ===\n";
foreach ($collection->listIndexes() as $index) {
    echo json_encode($index, JSON_PRETTY_PRINT) . "\n";
}

// 2. Count documents with null id
$nullCount = $collection->countDocuments(['id' => null]);
echo "\n=== Documents with id=null: $nullCount ===\n";

// 3. Count total documents
$totalCount = $collection->countDocuments([]);
echo "=== Total documents: $totalCount ===\n";

// 4. Drop the problematic id_1 index if it exists
echo "\n=== Dropping id_1 index... ===\n";
try {
    $collection->dropIndex('id_1');
    echo "Dropped id_1 index successfully.\n";
} catch (\Exception $e) {
    echo "Could not drop id_1: " . $e->getMessage() . "\n";
}

// 5. Clear all sessions (they are transient data, safe to remove)
echo "\n=== Clearing all session documents... ===\n";
$result = $collection->deleteMany([]);
echo "Deleted " . $result->getDeletedCount() . " documents.\n";

// 6. List indexes after fix
echo "\n=== Indexes After Fix ===\n";
foreach ($collection->listIndexes() as $index) {
    echo json_encode($index, JSON_PRETTY_PRINT) . "\n";
}

echo "\nDone! The sessions collection has been cleaned up.\n";
