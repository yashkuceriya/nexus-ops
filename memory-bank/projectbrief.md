# Project Brief — NexusOps

## Overview
NexusOps is a **production-grade facility operations platform** built in Laravel/PHP. Portfolio/interview project targeting the building commissioning and facility management industry. Demonstrates deep domain knowledge of facility management, IoT integration, predictive maintenance, and enterprise SaaS architecture.

## Core Goals
1. Showcase full-stack Laravel expertise (Livewire, multi-tenancy, API design, queue-driven architecture)
2. Demonstrate understanding of facility management workflows: commissioning → closeout → operations
3. Show ability to build production-ready software with proper auth, policies, notifications, CI/CD
4. Impress with high-tech UI: interactive maps, AI insights, real-time dashboards, command palette
5. Deployable on AWS Fargate with Docker

## Key Constraints
- **Branding**: Called "NexusOps" exclusively. Zero references to the prior upstream vendor anywhere in the codebase (UI, comments, class names, namespaces, DB columns, seed values, docs). See `CLAUDE.md` for the exact forbidden-string list.
- **SQLite for dev**: Avoid MySQL-specific functions (use CASE WHEN not FIELD(), strftime not DATE_FORMAT)
- **CDN-based frontend**: Tailwind CSS, Alpine.js, Chart.js, Mapbox GL loaded via CDN — no build step needed
- **Demo-ready**: Ships with realistic seed data (3 projects, 6 assets, sensors, work orders, vendors, automation rules)
