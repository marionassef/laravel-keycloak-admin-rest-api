# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.1.0] - 2026-01-29

### Added
- **Organization API Support** - Full support for Keycloak Organizations (requires Keycloak 24+)
  - `create()` - Create a new organization
  - `all()` - List all organizations
  - `get()` - Get organization by ID
  - `update()` - Update organization
  - `delete()` - Delete organization
  - `getMembers()` - Get organization members
  - `addMember()` - Add member to organization
  - `removeMember()` - Remove member from organization
  - `inviteUser()` - Invite user to organization
  - `getIdentityProviders()` - Get organization's identity providers
  - `linkIdentityProvider()` - Link an IdP to organization
  - `unlinkIdentityProvider()` - Unlink an IdP from organization
  - `getUserOrganizations()` - Get organizations for a user
  - `search()` - Search organizations

- **Comprehensive Test Suite** - 132 tests with 100% code coverage
  - Unit tests for all services
  - Feature tests for service provider and configuration
  - PHPUnit configuration with coverage support

### Fixed
- **Critical Bug Fix**: `client_roles` config had incorrect keys (`'update'` instead of `'api'`) for update and delete endpoints
- **Critical Bug Fix**: `client_roles` update method was using `'post'` instead of `'put'`
- **Bug Fix**: `Client::response()` was incorrectly calling `getByName()` instead of `get()`
- **Bug Fix**: `GroupService::addUser()`, `removeUser()`, and `search()` methods were not passing arguments correctly to `__call()`
- **Bug Fix**: Cache key typo `'keycload-admin-credentials'` corrected to `'keycloak-admin-credentials'` (backward compatible)
- **Bug Fix**: Duplicate query string handling in `Service::initApi()` removed

### Changed
- Minimum PHP version requirement: PHP 8.0+
- Updated Guzzle requirement to ^7.0
- Added Laravel 9, 10, and 11 support via `illuminate/support`

### Backward Compatibility
- Cache key migration is automatic - existing cached credentials with the old key will be migrated to the new key
- All existing API methods remain unchanged
- New Organization service is purely additive

## [2.0.2] - Previous Release

See git history for changes in previous versions.

