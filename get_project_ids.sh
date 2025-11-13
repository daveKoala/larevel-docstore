#!/bin/bash

# Simple script to get all project GUIDs

echo "====================================="
echo "Project IDs (GUIDs) in the system"
echo "====================================="
echo ""

docker-compose exec app php artisan tinker --execute="
App\Models\Project::with('organization')->get()->each(function(\$project) {
    echo sprintf(
        '%-35s | GUID: %s | Org: %s' . PHP_EOL,
        \$project->name,
        \$project->guid,
        \$project->organization->name
    );
});
"

echo ""
echo "====================================="
echo ""
echo "To use in your API requests:"
echo "  curl -X POST http://localhost:8001/api/orders \\"
echo "    -H \"Authorization: Bearer YOUR_TOKEN\" \\"
echo "    -H \"Content-Type: application/json\" \\"
echo "    -d '{\"project_guid\":\"PASTE_GUID_HERE\",\"details\":\"Your order\"}'"
