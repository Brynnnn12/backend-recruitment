# Authenticating requests

To authenticate requests, include an **`Authorization`** header with the value **`"Bearer {TOKEN_AUTENTIKASI_ANDA}"`**.

All authenticated endpoints are marked with a `requires authentication` badge in the documentation below.

Anda dapat mengambil token dengan login melalui endpoint <code>POST /api/login</code>. Token akan berlaku selama sesi Anda.
