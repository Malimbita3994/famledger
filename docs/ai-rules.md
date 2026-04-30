# AI Development Enforcement Guide (Cursor)

## 1) Purpose

This document defines architectural standards and coding rules for AI-assisted development.

Goals:
- Ensure consistent system architecture
- Maintain high-quality, maintainable code
- Speed up development through standards

## 2) Core Development Principle

The AI must act as a senior software architect, not only as a code generator.

Before any change, the AI must:
- Analyze the existing structure
- Validate changes against these rules
- Refactor where needed
- Produce clean, scalable code

## 3) Architecture Rules

### 3.1 Layered Architecture

Use service-based layering:
- Controllers: request/response orchestration only
- Services: business logic
- Repositories: data access
- Models: data structure and relationships

Hard rules:
- No business logic in controllers
- No direct database queries in controllers

### 3.2 API Standardization

All API responses must use:

```json
{
  "success": true,
  "message": "Description",
  "data": {}
}
```

### 3.3 Data Handling

- Use DTOs for input transfer where applicable
- Validate all incoming data
- Sanitize input before processing

### 3.4 Error Handling

- Use centralized/global error handling
- Do not expose raw errors
- Return structured error responses

### 3.5 Naming Conventions

- Variables and methods: `camelCase`
- Database fields: `snake_case`
- Modules and classes: clear, descriptive names

### 3.6 Security Rules

- Authentication and authorization via middleware/policies/guards
- No hardcoded credentials
- Validate and authorize all requests

## 4) AI Enforcement Instructions

When editing any file, the AI must:
- Analyze code against this document
- Identify violations
- Refactor to comply fully
- Improve structure and readability
- Preserve modular and scalable design
- Apply changes directly with minimal non-essential explanation

## 5) Code Quality Standards

The AI must ensure:
- Clean, readable code
- Reusable components
- Minimal duplication
- Proper separation of concerns

## 6) Module Development Rule

When creating a new module, include:
- Controller
- Service
- Repository
- Validation and error handling
- API response consistency

## 7) Continuous Refactoring Policy

The AI should continuously:
- Improve existing code
- Refactor outdated structures
- Align touched code with this standard

## 8) Usage in Cursor

To enforce this guide manually:
1. Highlight code or file
2. Press `Ctrl + K`
3. Use:

```text
Follow /docs/ai-rules.md strictly.
Analyze, refactor, and improve this code to meet all defined standards.
Apply changes directly.
```

## 9) Expected Outcome

Following this guide should produce:
- Scalable architecture
- Clean, maintainable codebase
- Faster development cycles
- Reduced technical debt
