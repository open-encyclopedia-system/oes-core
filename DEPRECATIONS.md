# Currently Deprecated (Pending Removal)

| Type           | Item                                 | Deprecated in | Planned removal | Replacement                              |
|----------------|--------------------------------------|---------------|-----------------|------------------------------------------|
| Class          | `OES_Taxonomy`                       | 3.0.0         | -               | `OES_Term`                               |
| Function       | `\OES\Admin\Tools\display_tool()`    | 3.0.0         | -               | `\OES\Admin\Tools\display()`             |
| Function       | `read_data_from_project_json_file()` | 3.0.0         | -               | `read_data_from_application_json_file()` |
| Function       | `\OES\ACF\oes_get_field()`           | 3.0.0         | -               | `oes_get_field()`                        |
| Function       | `\OES\ACF\get_field_display_value()` | 3.0.0         | -               | `oes_get_field_display_value()`          |
| Function       | `oes_get_project_name()`             | 3.0.0         | -               | `oes_get_application_name()`             |
| Function       | `oes_get_project_class_name()`       | 3.0.0         | -               | `oes_get_application_class_name()`       |
| Function       | `oes_get_project_view()`             | 3.0.0         | -               | `oes_get_application_view()`             |
| Function       | `oes_normalize_path_for_localhost()` | 3.0.0         | -               | `oes_strip_path_prefix()`                |
| Function       | `oes_include_project()`              | 3.0.0         | -               | `oes_include_application()`              |
| Function       | `oes_get_highlighted_search()`       | 3.0.0         | -               | Class `OES_Search_Results`               |
| Function       | `oes_starts_with()`                  | 3.0.0         | -               | `str_starts_with()`                      |
| Function       | `oes_ends_with()`                    | 3.0.0         | -               | `str_ends_with()`                        |
| Method         | `OES::initialize_project()`          | 3.0.0         | -               | `OES::initialize_application()`          |
| Filter         | `oes/project_options`                | 3.0.0         | -               | `oes/application_options`                |
| Function       | `oes_add_project_script()`           | 3.0.0         | -               | `oes_add_application_script()`           |
| Parameter      | `OES::$project_initialized`          | 3.0.0         | -               | `OES::$application_initialized`          |
| Constant       | `OES_PROJECT_PLUGIN`                 | 3.0.0         | -               | `OES_APPLICATION_PLUGIN`                 |
| Constant       | `OES_BASENAME_PROJECT`               | 3.0.0         | -               | `OES_BASENAME_APPLICATION`               |
| Class property | `OES_Core::$project_initialized`     | 3.0.0         | -               | `OES_Core::$application_initialized`     |