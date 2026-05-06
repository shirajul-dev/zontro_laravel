class Validators {
  const Validators._();

  static String? requiredField(String? value, {String field = 'Field'}) {
    if (value == null || value.trim().isEmpty) {
      return '$field is required.';
    }
    return null;
  }

  static String? validUrl(String? value) {
    final String? requiredError = requiredField(value, field: 'URL');
    if (requiredError != null) {
      return requiredError;
    }

    final Uri? uri = Uri.tryParse(value!.trim());
    final bool isValid = uri != null && uri.hasScheme && uri.host.isNotEmpty;

    return isValid ? null : 'Please enter a valid URL.';
  }
}
