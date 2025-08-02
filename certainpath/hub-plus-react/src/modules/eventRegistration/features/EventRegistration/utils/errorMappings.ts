const errorCodeToMessage: Record<string, string> = {
  I_WC_01: "Request processed successfully.",
  E_WC_01:
    "Please include the Authorize.net Accept.js library from the official CDN.",
  E_WC_02: "A HTTPS connection is required to use Accept.js.",
  E_WC_03:
    "Accept.js is not loaded correctly. Ensure the script is from Authorize.net's servers.",
  E_WC_04: "Some required payment fields are missing.",
  E_WC_05: "Invalid credit card number. Please verify and try again.",
  E_WC_06: "Please provide a valid expiration month (01-12).",
  E_WC_07: "Please provide a valid expiration year (YY or YYYY).",
  E_WC_08: "Expiration date must be in the future.",
  E_WC_10: "Please provide a valid apiLoginID.",
  E_WC_13:
    "Invalid fingerprint. The Accept.js script may be outdated or cached incorrectly.",
  E_WC_14: "Encryption failed during card data submission. Please try again.",
  E_WC_15: "Invalid CVV. Must be 3 or 4 digits.",
  E_WC_16: "Please provide a valid ZIP code (up to 20 characters).",
  E_WC_17: "Please provide a valid cardholder name (up to 64 characters).",
  E_WC_18:
    "Client Key is required. Obtain it from Authorize.net's Merchant Interface.",
  E_WC_19:
    "An error occurred during processing. Check your API Login ID and environment (Sandbox vs Production).",
  E_WC_21:
    "User authentication failed. The API Login ID or Public Client Key is incorrect.",
  E_WC_23:
    "Please provide either card information or bank information, not both.",
  E_WC_24: "Invalid bank account number (max 17 digits).",
  E_WC_25: "Invalid bank routing number (must be 9 digits).",
  E_WC_26: "Please provide a valid account holder name (max 22 characters).",
  E_WC_27:
    "Please provide a valid account type: checking, savings, or businessChecking.",
};

export default errorCodeToMessage;
