class TemplateEngine {
  const TemplateEngine._();

  static String render(String template, Map<String, String> values) {
    String result = template;
    values.forEach((String key, String value) {
      result = result.replaceAll('%$key%', value);
    });
    return result;
  }
}
