# Todo_API
an API for a ToDo app

# Quadrant Todo API

A todo API built with PHP 8.2 and MySQL. Each todo belongs to one of four quadrants (Eisenhower Matrix), so you can see what to do first.

All requests and responses are JSON. Base path is `/api/v1`.

Valid quadrant values:

- `important_urgent`
- `important_not_urgent`
- `not_important_urgent`
- `not_important_not_urgent`

Any other value returns 400.

## Response format

Every response has the same shape.

Success:

```json
{ "success": true, "data": { } }
```

Error:

```json
{
  "success": false,
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "The quadrant value is not valid.",
    "fields": { "quadrant": "Use one of the four keys." }
  }
}
```

`fields` is only there for validation errors. Logout and delete return 204 with no body.

Error codes:

- 400 `VALIDATION_ERROR` - input is not valid
- 401 `UNAUTHORIZED` - token is missing, unknown or expired
- 401 `INVALID_CREDENTIALS` - wrong email or password
- 404 `NOT_FOUND` - todo does not exist or belongs to someone else
- 409 `CONFLICT` - clashes with current state, like a taken email or a completed todo
- 413 `PAYLOAD_TOO_LARGE` - body is over 64 KB
- 429 `TOO_MANY_REQUESTS` - 10 failed logins for one email in 15 minutes
- 500 `SERVER_ERROR` - something unexpected, details are only written to the log

## Middleware

The auth middleware runs before logout and all todo endpoints. It does this:

1. Reads the token from the `Authorization: Bearer <token>` header. If it is missing, returns 401.
2. Hashes the token and looks it up in the `auth_tokens` table. If it is not found, returns 401.
3. Checks the expiry date. Tokens last 30 days. If it has expired, the row is deleted and it returns 401.
4. Attaches the user id to the request.

After this, every todo query is limited to that user id. Asking for another user's todo gives the same 404 as a todo that does not exist.

The token is shown once, when you log in. Only its hash is stored. Logging out deletes the row, so the token stops working straight away. Logging in on a second device creates a second token and leaves the first one alone.

## Endpoints

Everything except register and login needs this header:

```
Authorization: Bearer <token>
```

### POST /auth/register

```json
{ "email": "ada@example.com", "password": "correct-horse-battery" }
```

Password must be 10 to 200 characters. Returns 201 with `{ "id": 7, "email": "ada@example.com" }`. A taken email returns 409.

### POST /auth/login

```json
{ "email": "ada@example.com", "password": "correct-horse-battery" }
```

Returns 200 with the token:

```json
{
  "token": "a3f1...9c2e",
  "token_type": "Bearer",
  "expires_at": "2026-09-23T10:00:00Z"
}
```

### POST /auth/logout

No body. Returns 204. Only ends the session for the token you sent.

### POST /todos

```json
{
  "title": "Send the report to finance",
  "description": "Include the Q3 numbers.",
  "quadrant": "important_urgent",
  "due_date": "2026-08-25"
}
```

Title is 1 to 255 characters and required. Description is optional, up to 5000. Due date is `YYYY-MM-DD`. Returns 201 with the todo.

### GET /todos

Returns your todos, sorted by due date and then quadrant. Optional query parameters: `quadrant`, `status`, `due_date`, `page`, `per_page` (max 100).

```json
{
  "items": [ ],
  "page": 1,
  "per_page": 20,
  "total": 57
}
```

### GET /todos/{id}

Returns 200 with the todo.

### PATCH /todos/{id}

Send only the fields you want to change: `title`, `description`, `quadrant`, `status` or `due_date`. Returns 200 with the updated todo. Setting `status` to `completed` sets `completed_at`. Setting it back to `pending` clears it.

### DELETE /todos/{id}

Returns 204.

### POST /todos/{id}/carry-over

For a todo that slipped past its date.

```json
{ "new_due_date": "2026-08-26" }
```

`new_due_date` is optional and defaults to today. It can't be in the past. Adds 1 to `carry_count`. Returns 200 with the todo.

### POST /todos/carry-over

Carries over every pending todo from one day, in a single transaction.

```json
{ "from_date": "2026-08-23", "new_due_date": "2026-08-24" }
```

`from_date` is required. `new_due_date` is optional and defaults to today. Each todo gets `carry_count` plus 1. Returns 200:

```json
{ "carried_over": 5, "items": [ ] }
```

### POST /todos/{id}/postpone

For a todo the user wants to reschedule on purpose.

```json
{ "new_due_date": "2026-08-30" }
```

`new_due_date` is required and must be later than the current due date. Adds 1 to `postpone_count` and leaves `carry_count` alone. Returns 200 with the todo.

Carry-over and postpone only work on pending todos. A completed todo returns 409. Neither one changes the quadrant, use PATCH for that.

## Todo object

```json
{
  "id": 42,
  "title": "Send the report to finance",
  "description": "Include the Q3 numbers.",
  "quadrant": "important_urgent",
  "status": "pending",
  "due_date": "2026-08-25",
  "carry_count": 2,
  "postpone_count": 1,
  "completed_at": null,
  "created_at": "2026-08-20T09:15:00Z",
  "updated_at": "2026-08-24T07:00:00Z"
}
```
