<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Initial database schema for Zagros CMMS
 */
final class Version20260128000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create initial database schema for CMMS';
    }

    public function up(Schema $schema): void
    {
        // Users and Roles
        $this->addSql('CREATE TABLE users (
            id UUID PRIMARY KEY,
            email VARCHAR(180) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            first_name VARCHAR(100) NOT NULL,
            last_name VARCHAR(100) NOT NULL,
            phone VARCHAR(20),
            roles JSON NOT NULL,
            is_active BOOLEAN DEFAULT true,
            created_at TIMESTAMP NOT NULL,
            updated_at TIMESTAMP NOT NULL
        )');
        $this->addSql('CREATE INDEX idx_users_email ON users(email)');

        // Asset Categories
        $this->addSql('CREATE TABLE asset_categories (
            id UUID PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            parent_id UUID REFERENCES asset_categories(id) ON DELETE SET NULL,
            description TEXT,
            icon VARCHAR(50),
            created_at TIMESTAMP NOT NULL
        )');

        // Locations
        $this->addSql('CREATE TABLE locations (
            id UUID PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            parent_id UUID REFERENCES locations(id) ON DELETE SET NULL,
            address TEXT,
            coordinates POINT,
            type VARCHAR(50),
            created_at TIMESTAMP NOT NULL
        )');

        // Assets
        $this->addSql('CREATE TABLE assets (
            id UUID PRIMARY KEY,
            code VARCHAR(50) UNIQUE NOT NULL,
            name VARCHAR(200) NOT NULL,
            category_id UUID REFERENCES asset_categories(id) ON DELETE SET NULL,
            location_id UUID REFERENCES locations(id) ON DELETE SET NULL,
            serial_number VARCHAR(100),
            manufacturer VARCHAR(100),
            model VARCHAR(100),
            purchase_date DATE,
            purchase_cost DECIMAL(12,2),
            warranty_expiry DATE,
            status VARCHAR(20) NOT NULL DEFAULT \'operational\',
            criticality_level INTEGER NOT NULL DEFAULT 3,
            qr_code TEXT,
            metadata JSONB,
            created_at TIMESTAMP NOT NULL,
            updated_at TIMESTAMP NOT NULL,
            CONSTRAINT chk_criticality CHECK (criticality_level BETWEEN 1 AND 5)
        )');
        $this->addSql('CREATE INDEX idx_assets_code ON assets(code)');
        $this->addSql('CREATE INDEX idx_assets_category ON assets(category_id)');
        $this->addSql('CREATE INDEX idx_assets_location ON assets(location_id)');
        $this->addSql('CREATE INDEX idx_assets_status ON assets(status)');
        $this->addSql('CREATE INDEX idx_assets_metadata ON assets USING GIN(metadata)');

        // Asset Hierarchy
        $this->addSql('CREATE TABLE asset_hierarchy (
            parent_id UUID REFERENCES assets(id) ON DELETE CASCADE,
            child_id UUID REFERENCES assets(id) ON DELETE CASCADE,
            relationship_type VARCHAR(50),
            PRIMARY KEY (parent_id, child_id)
        )');

        // Asset Meters
        $this->addSql('CREATE TABLE asset_meters (
            id UUID PRIMARY KEY,
            asset_id UUID REFERENCES assets(id) ON DELETE CASCADE,
            meter_type VARCHAR(50) NOT NULL,
            current_value DECIMAL(12,2) NOT NULL,
            unit VARCHAR(20) NOT NULL,
            last_reading_date TIMESTAMP NOT NULL
        )');

        // Asset Documents
        $this->addSql('CREATE TABLE asset_documents (
            id UUID PRIMARY KEY,
            asset_id UUID REFERENCES assets(id) ON DELETE CASCADE,
            title VARCHAR(200) NOT NULL,
            file_path TEXT NOT NULL,
            file_type VARCHAR(50),
            uploaded_by UUID REFERENCES users(id) ON DELETE SET NULL,
            uploaded_at TIMESTAMP NOT NULL
        )');

        // Asset History
        $this->addSql('CREATE TABLE asset_history (
            id UUID PRIMARY KEY,
            asset_id UUID REFERENCES assets(id) ON DELETE CASCADE,
            action VARCHAR(50) NOT NULL,
            field_changed VARCHAR(100),
            old_value TEXT,
            new_value TEXT,
            changed_by UUID REFERENCES users(id) ON DELETE SET NULL,
            changed_at TIMESTAMP NOT NULL
        )');

        // Work Orders
        $this->addSql('CREATE TABLE work_orders (
            id UUID PRIMARY KEY,
            number VARCHAR(50) UNIQUE NOT NULL,
            title VARCHAR(200) NOT NULL,
            description TEXT,
            asset_id UUID REFERENCES assets(id) ON DELETE SET NULL,
            type VARCHAR(50) NOT NULL,
            priority INTEGER NOT NULL DEFAULT 3,
            status VARCHAR(50) NOT NULL DEFAULT \'pending\',
            assigned_to UUID REFERENCES users(id) ON DELETE SET NULL,
            created_by UUID REFERENCES users(id) ON DELETE SET NULL,
            due_date TIMESTAMP,
            scheduled_start TIMESTAMP,
            actual_start TIMESTAMP,
            actual_end TIMESTAMP,
            estimated_hours DECIMAL(6,2),
            actual_hours DECIMAL(6,2),
            created_at TIMESTAMP NOT NULL,
            updated_at TIMESTAMP NOT NULL,
            completed_at TIMESTAMP,
            CONSTRAINT chk_priority CHECK (priority BETWEEN 1 AND 5),
            CONSTRAINT chk_dates CHECK (actual_end IS NULL OR actual_end >= actual_start)
        )');
        $this->addSql('CREATE INDEX idx_wo_number ON work_orders(number)');
        $this->addSql('CREATE INDEX idx_wo_status ON work_orders(status)');
        $this->addSql('CREATE INDEX idx_wo_assigned ON work_orders(assigned_to)');
        $this->addSql('CREATE INDEX idx_wo_asset ON work_orders(asset_id)');
        $this->addSql('CREATE INDEX idx_wo_due_date ON work_orders(due_date)');

        // Work Order Tasks
        $this->addSql('CREATE TABLE work_order_tasks (
            id UUID PRIMARY KEY,
            work_order_id UUID REFERENCES work_orders(id) ON DELETE CASCADE,
            title VARCHAR(200) NOT NULL,
            description TEXT,
            sequence INTEGER NOT NULL,
            is_completed BOOLEAN DEFAULT false,
            completed_by UUID REFERENCES users(id) ON DELETE SET NULL,
            completed_at TIMESTAMP
        )');

        // Work Order Attachments
        $this->addSql('CREATE TABLE work_order_attachments (
            id UUID PRIMARY KEY,
            work_order_id UUID REFERENCES work_orders(id) ON DELETE CASCADE,
            file_path TEXT NOT NULL,
            file_name VARCHAR(255) NOT NULL,
            file_type VARCHAR(50),
            uploaded_by UUID REFERENCES users(id) ON DELETE SET NULL,
            uploaded_at TIMESTAMP NOT NULL
        )');

        // Work Order Time Logs
        $this->addSql('CREATE TABLE work_order_time_logs (
            id UUID PRIMARY KEY,
            work_order_id UUID REFERENCES work_orders(id) ON DELETE CASCADE,
            user_id UUID REFERENCES users(id) ON DELETE SET NULL,
            start_time TIMESTAMP NOT NULL,
            end_time TIMESTAMP,
            duration_minutes INTEGER,
            notes TEXT
        )');

        // Work Order Comments
        $this->addSql('CREATE TABLE work_order_comments (
            id UUID PRIMARY KEY,
            work_order_id UUID REFERENCES work_orders(id) ON DELETE CASCADE,
            user_id UUID REFERENCES users(id) ON DELETE SET NULL,
            comment TEXT NOT NULL,
            created_at TIMESTAMP NOT NULL
        )');

        // Maintenance Schedules
        $this->addSql('CREATE TABLE maintenance_schedules (
            id UUID PRIMARY KEY,
            name VARCHAR(200) NOT NULL,
            asset_id UUID REFERENCES assets(id) ON DELETE CASCADE,
            frequency_type VARCHAR(20) NOT NULL,
            interval_value INTEGER NOT NULL,
            interval_unit VARCHAR(20) NOT NULL,
            next_due_date TIMESTAMP,
            last_generated_at TIMESTAMP,
            is_active BOOLEAN DEFAULT true,
            created_at TIMESTAMP NOT NULL
        )');

        // Maintenance Templates
        $this->addSql('CREATE TABLE maintenance_templates (
            id UUID PRIMARY KEY,
            name VARCHAR(200) NOT NULL,
            description TEXT,
            category_id UUID REFERENCES asset_categories(id) ON DELETE SET NULL,
            estimated_hours DECIMAL(6,2),
            tasks JSONB,
            created_at TIMESTAMP NOT NULL
        )');

        // Inventory Categories
        $this->addSql('CREATE TABLE inventory_categories (
            id UUID PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            parent_id UUID REFERENCES inventory_categories(id) ON DELETE SET NULL
        )');

        // Parts
        $this->addSql('CREATE TABLE parts (
            id UUID PRIMARY KEY,
            part_number VARCHAR(100) UNIQUE NOT NULL,
            name VARCHAR(200) NOT NULL,
            description TEXT,
            category_id UUID REFERENCES inventory_categories(id) ON DELETE SET NULL,
            quantity INTEGER NOT NULL DEFAULT 0,
            unit VARCHAR(20) NOT NULL,
            unit_price DECIMAL(12,2),
            min_stock_level INTEGER,
            max_stock_level INTEGER,
            location VARCHAR(100),
            created_at TIMESTAMP NOT NULL,
            updated_at TIMESTAMP NOT NULL,
            CONSTRAINT chk_quantity CHECK (quantity >= 0)
        )');
        $this->addSql('CREATE INDEX idx_parts_number ON parts(part_number)');
        $this->addSql('CREATE INDEX idx_parts_category ON parts(category_id)');

        // Inventory Transactions
        $this->addSql('CREATE TABLE inventory_transactions (
            id UUID PRIMARY KEY,
            part_id UUID REFERENCES parts(id) ON DELETE CASCADE,
            transaction_type VARCHAR(20) NOT NULL,
            quantity INTEGER NOT NULL,
            unit_price DECIMAL(12,2),
            reference_type VARCHAR(50),
            reference_id UUID,
            notes TEXT,
            created_by UUID REFERENCES users(id) ON DELETE SET NULL,
            created_at TIMESTAMP NOT NULL
        )');

        // Work Order Parts
        $this->addSql('CREATE TABLE work_order_parts (
            id UUID PRIMARY KEY,
            work_order_id UUID REFERENCES work_orders(id) ON DELETE CASCADE,
            part_id UUID REFERENCES parts(id) ON DELETE CASCADE,
            quantity_used INTEGER NOT NULL,
            unit_price DECIMAL(12,2)
        )');

        // Downtime Logs
        $this->addSql('CREATE TABLE downtime_logs (
            id UUID PRIMARY KEY,
            asset_id UUID REFERENCES assets(id) ON DELETE CASCADE,
            work_order_id UUID REFERENCES work_orders(id) ON DELETE SET NULL,
            start_time TIMESTAMP NOT NULL,
            end_time TIMESTAMP,
            duration_minutes INTEGER,
            reason TEXT,
            impact VARCHAR(50)
        )');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE IF EXISTS downtime_logs CASCADE');
        $this->addSql('DROP TABLE IF EXISTS work_order_parts CASCADE');
        $this->addSql('DROP TABLE IF EXISTS inventory_transactions CASCADE');
        $this->addSql('DROP TABLE IF EXISTS parts CASCADE');
        $this->addSql('DROP TABLE IF EXISTS inventory_categories CASCADE');
        $this->addSql('DROP TABLE IF EXISTS maintenance_templates CASCADE');
        $this->addSql('DROP TABLE IF EXISTS maintenance_schedules CASCADE');
        $this->addSql('DROP TABLE IF EXISTS work_order_comments CASCADE');
        $this->addSql('DROP TABLE IF EXISTS work_order_time_logs CASCADE');
        $this->addSql('DROP TABLE IF EXISTS work_order_attachments CASCADE');
        $this->addSql('DROP TABLE IF EXISTS work_order_tasks CASCADE');
        $this->addSql('DROP TABLE IF EXISTS work_orders CASCADE');
        $this->addSql('DROP TABLE IF EXISTS asset_history CASCADE');
        $this->addSql('DROP TABLE IF EXISTS asset_documents CASCADE');
        $this->addSql('DROP TABLE IF EXISTS asset_meters CASCADE');
        $this->addSql('DROP TABLE IF EXISTS asset_hierarchy CASCADE');
        $this->addSql('DROP TABLE IF EXISTS assets CASCADE');
        $this->addSql('DROP TABLE IF EXISTS locations CASCADE');
        $this->addSql('DROP TABLE IF EXISTS asset_categories CASCADE');
        $this->addSql('DROP TABLE IF EXISTS users CASCADE');
    }
}
