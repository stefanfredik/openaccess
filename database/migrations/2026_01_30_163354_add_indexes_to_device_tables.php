<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tables = [
            'ad_routers' => ['name', 'code', 'serial_number', 'ip_address'],
            'ad_olts' => ['name', 'code', 'serial_number', 'ip_address'],
            'ad_onts' => ['name', 'code', 'serial_number', 'ip_address', 'mac_address'],
            'ad_switches' => ['name', 'code', 'serial_number', 'ip_address', 'mac_address'],
            'ad_access_points' => ['name', 'code', 'serial_number', 'ip_address', 'mac_address'],
            'cpes' => ['name', 'code', 'serial_number', 'mac_address'],
            'pd_poles' => ['name', 'code'],
            'pd_odps' => ['name', 'code'],
            'pd_odfs' => ['name', 'code'],
            'pd_joint_boxes' => ['name', 'code'],
            'pd_splicing_points' => ['name', 'code'],
            'pd_splitters' => ['name', 'code'],
            'pd_cables' => ['name', 'code'],
            'pd_slacks' => ['name', 'code'],
            'pd_towers' => ['name', 'code'],
        ];

        foreach ($tables as $tableName => $columns) {
            if (Schema::hasTable($tableName)) {
                foreach ($columns as $column) {
                    // Check if column exists
                    if (Schema::hasColumn($tableName, $column)) {
                        // Check if index exists. Laravel default index name is table_column_index
                        $indexName = strtolower($tableName . '_' . $column . '_index');
                        
                        // We use a try-catch block or check existence. 
                        // Schema::hasIndex is available in recent Laravel versions.
                        if (!Schema::hasIndex($tableName, $indexName)) {
                             Schema::table($tableName, function (Blueprint $table) use ($column) {
                                $table->index($column);
                             });
                        }
                    }
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'ad_routers' => ['name', 'code', 'serial_number', 'ip_address'],
            'ad_olts' => ['name', 'code', 'serial_number', 'ip_address'],
            'ad_onts' => ['name', 'code', 'serial_number', 'ip_address', 'mac_address'],
            'ad_switches' => ['name', 'code', 'serial_number', 'ip_address', 'mac_address'],
            'ad_access_points' => ['name', 'code', 'serial_number', 'ip_address', 'mac_address'],
            'cpes' => ['name', 'code', 'serial_number', 'mac_address'],
            'pd_poles' => ['name', 'code'],
            'pd_odps' => ['name', 'code'],
            'pd_odfs' => ['name', 'code'],
            'pd_joint_boxes' => ['name', 'code'],
            'pd_splicing_points' => ['name', 'code'],
            'pd_splitters' => ['name', 'code'],
            'pd_cables' => ['name', 'code'],
            'pd_slacks' => ['name', 'code'],
            'pd_towers' => ['name', 'code'],
        ];

        foreach ($tables as $table => $columns) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $table) use ($columns) {
                    foreach ($columns as $column) {
                         if (Schema::hasColumn($table->getTable(), $column)) {
                            $table->dropIndex([$column]);
                         }
                    }
                });
            }
        }
    }
};
