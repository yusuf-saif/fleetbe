# Authenticating requests

To authenticate requests, include an **`Authorization`** header with the value **`"Bearer {token}"`**.

All authenticated endpoints are marked with a `requires authentication` badge in the documentation below.

Send your Sanctum token in the <code>Authorization: Bearer &lt;token&gt;</code> header.
