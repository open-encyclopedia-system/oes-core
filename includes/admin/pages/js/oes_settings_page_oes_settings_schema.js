(function (oesPattern, $, undefined) {


    /* @param The pattern ID. */
    let pattern_ID = false;


    /**
     * Initialize the popup with the pattern configuration.
     */
    oesPattern.InitPanel = function (id) {
        pattern_ID = id.substring(1);
        oesAdminPopup.get();
        oesAdminPopup.setTitle(getLabel());
        setContent();
        oesAdminPopup.show();
    };


    /**
     * Create a new row for the pattern configuration table.
     */
    oesPattern.addNewRow = function () {
        const table = document.getElementById('oes-pattern-definition');
        if (table) {
            let tbody = table.getElementsByTagName('tbody')[0];
            if(tbody === undefined) tbody = table.createTBody();
            addRow(tbody, ((table.rows.length - 1) / 2 + 1), []);
        }
    }


    /**
     * Delete this pattern row.
     */
    oesPattern.rowDelete = function (el) {
        const row = $(el).parents('tr:first');
        row.next().remove();
        row.remove();
    }


    /**
     * Move this pattern row upwards.
     */
    oesPattern.rowUp = function (el) {
        const row = $(el).parents('tr:first'),
            config_row = row.next(),
            previous = row.prev().prev();
        row.insertBefore(previous);
        config_row.insertBefore(previous);
    }


    /**
     * Move this pattern row downwards.
     */
    oesPattern.rowDown = function (el) {
        const row = $(el).parents('tr:first'),
            config_row = row.next(),
            next = config_row.next().next();
        config_row.insertAfter(next);
        row.insertAfter(next);
    }


    /**
     * Trigger the config row.
     */
    oesPattern.triggerRow = function (el) {
        $(el).parent().parent().next().toggleClass("active");
    }


    /**
     * Save the pattern.
     */
    oesPattern.save = function () {

        if (pattern_ID) {
            const cell = document.getElementById(pattern_ID + '-pattern');
            if (cell) {

                /* create pattern value */
                cell.value = JSON.stringify(getPatternArray());

                /* create preview */
                let pattern_trigger = document.getElementById('_' + pattern_ID);
                if (pattern_trigger) {
                    const trigger = $('.oes-pattern-trigger');
                    if(trigger && trigger.length > 0){
                        let trigger_text = '';
                        for(let i = 0; i < trigger.length; i++)
                            trigger_text += trigger[i].innerHTML;
                        pattern_trigger.innerHTML = trigger_text;
                    }
                }
            }
        }
        oesAdminPopup.hide();
    }


    /**
     * Get the label for the popup title.
     */
    function getLabel() {
        const label = $('#' + pattern_ID + '_label');
        let label_str = wp.i18n.__('Generate Pattern', 'oes');
        if (label && label.length > 0) label_str += ' ' + wp.i18n.__('for the Field', 'oes') + '"' + label[0].innerHTML + '"';
        return label_str;
    }


    /**
     * Add pattern configuration content to popup.
     */
    function setContent() {

        let content_wrapper = $('.oes-admin-popup-content-wrapper');
        if (content_wrapper.length > 0) {

            /* remove current content, then add new content and wrap it */
            $('.oes-pattern-content-wrapper').remove();
            const content = document.createElement('div');
            content.setAttribute('class', 'oes-pattern-content-wrapper');
            content.appendChild(createTable());
            content.appendChild(createTools());
            content_wrapper[0].appendChild(content);
        }
    }


    /**
     * Create table with pattern configurations.
     */
    function createTable() {

        /* create table */
        const table = document.createElement('table');
        table.setAttribute('id', 'oes-pattern-definition');
        table.setAttribute('class', 'wp-list-table widefat fixed table-view-list');

        /* create header */
        const thead = table.createTHead(),
            trow = thead.insertRow();
        trow.insertCell().outerHTML = '<th>#</th>';
        trow.insertCell().outerHTML = '<th>' + wp.i18n.__('Label', 'oes') + '</th>';
        trow.insertCell().outerHTML = '<th>' + wp.i18n.__('Required', 'oes') + '</th>';

        /* add configurations according to existing pattern */
        const pattern_field = document.getElementById(pattern_ID + '-pattern');
        if (pattern_field) {
            const json_pattern = pattern_field.value.length > 1 ? JSON.parse(pattern_field.value) : [];
            if (json_pattern && json_pattern.length > 0) {
                const tbody = table.createTBody();
                for (let i = 0; i < json_pattern.length; i++) addRow(tbody, i + 1, json_pattern[i]);
            }
        }
        return table;
    }


    /**
     * Create a button to add a new pattern part.
     */
    function createTools() {

        const tools = document.createElement('div'),
            new_part_div = document.createElement('div'),
            new_part = document.createElement('button');
        new_part_div.setAttribute('class', 'oes-pattern-new-part-button');
        new_part.setAttribute('class', 'button button-secondary button-large');
        new_part.setAttribute('type', 'button');
        new_part.setAttribute('onClick', 'oesPattern.addNewRow()');
        new_part.innerHTML = wp.i18n.__('Add New Part', 'oes');
        new_part_div.append(new_part);
        tools.append(new_part_div);

        const save_div = document.createElement('div'),
            save = document.createElement('button');
        save.setAttribute('id', 'oes-admin-popup-save');
        save.setAttribute('class', 'button button-primary button-large');
        save.setAttribute('type', 'button');
        save.setAttribute('onClick', 'oesPattern.save()');
        save.innerHTML = wp.i18n.__('Save Pattern', 'oes');
        save_div.append(save);
        tools.append(save_div);

        return tools;
    }


    /**
     * Create a row for the pattern configuration table.
     */
    function addRow(tbody, i, values) {

        const defaults = {
            string_value: '',
            field_key: null,
            required: false,
            fallback: null,
            prefix: '',
            suffix: '',
            separator: ''
        };
        values = {...defaults, ...values};

        /* create rows */
        const trigger_row = tbody.insertRow(),
            config_row = tbody.insertRow();
        trigger_row.setAttribute('class', 'row-' + i + ' oes-pattern-trigger-row');
        config_row.setAttribute('class', 'row-' + i + ' oes-pattern-config-row');

        /* create inner table */
        const field_wrapper = document.createElement('div');
        field_wrapper.setAttribute('class', 'oes-pattern-field-wrapper');

        /* create fields */
        field_wrapper.appendChild(createField(
            i,
            'string_value',
            'text',
            wp.i18n.__('Static String', 'oes'),
            values.string_value,
            wp.i18n.__('Display a static string.', 'oes')));

        let field = createSelectField(values.field_key);
        const label = (values.field_key ? field.options[field.selectedIndex].innerHTML : wp.i18n.__('New Part', 'oes'));
        field_wrapper.appendChild(createField(
            i,
            'field_key',
            'select',
            wp.i18n.__('Field', 'oes'),
            values.field_key,
            wp.i18n.__('Display a field value.', 'oes'),
            field));

        field_wrapper.appendChild(createField(
            i,
            'required',
            'checkbox',
            wp.i18n.__('Value Required', 'oes'),
            values.required,
            wp.i18n.__('The field value is required. If the field value is empty and a fallback field is set, take ' +
                'the field value of the fallback field.', 'oes')));

        field_wrapper.appendChild(createField(
            i,
            'fallback',
            'select',
            wp.i18n.__('Fallback Field', 'oes'),
            values.fallback,
            wp.i18n.__('If field value is set, empty and required, use fallback field instead. If the fallback ' +
                'field value is empty as well, use static string as default.', 'oes')));

        field_wrapper.appendChild(createField(
            i,
            'prefix',
            'text',
            wp.i18n.__('Prefix', 'oes'),
            values.prefix,
            wp.i18n.__('Display prefix before part.', 'oes')));

        field_wrapper.appendChild(createField(
            i,
            'suffix',
            'text',
            wp.i18n.__('Suffix', 'oes'),
            values.suffix,
            wp.i18n.__('Display suffix after part.', 'oes')));

        field_wrapper.appendChild(createField(
            i,
            'separator',
            'text',
            wp.i18n.__('Separator', 'oes'),
            values.separator,
            wp.i18n.__('If field value is array, use this as separator.', 'oes')));

        /* prepare trigger */
        const trigger = document.createElement('a');
        trigger.setAttribute('href', 'javascript:void(0)');
        trigger.setAttribute('class', 'oes-pattern-trigger');
        trigger.setAttribute('onClick', 'oesPattern.triggerRow(this)');

        let trigger_text = '';
        if (values) {
            trigger_text += values.prefix + values.string_value;
            if (label && label !== '-') trigger_text += '[' + label + ']';
            trigger_text += values.suffix;
        } else trigger_text = 'New Part';
        trigger.innerHTML = trigger_text;

        /* prepare actions */
        const actions = document.createElement('div'),
            a = document.createElement('a'),
            aUp = document.createElement('a'),
            aDown = document.createElement('a');
        aUp.setAttribute('href', 'javascript:void(0)');
        aUp.setAttribute('onClick', 'oesPattern.rowUp(this)');
        aUp.innerHTML = wp.i18n.__('Up', 'oes');
        aDown.setAttribute('href', 'javascript:void(0)');
        aDown.setAttribute('onClick', 'oesPattern.rowDown(this)');
        aDown.innerHTML = wp.i18n.__('Down', 'oes');
        a.setAttribute('href', 'javascript:void(0)');
        a.setAttribute('onClick', 'oesPattern.rowDelete(this)');
        a.setAttribute('class', 'oes-pattern-delete-part');
        a.innerHTML = wp.i18n.__('Delete', 'oes');
        actions.setAttribute('class', 'oes-pattern-actions');
        actions.appendChild(aUp);
        actions.appendChild(aDown);
        actions.appendChild(a);

        /* create trigger */
        trigger_row.insertCell().innerHTML = i;
        let trigger_cell = trigger_row.insertCell();
        trigger_cell.setAttribute('class', 'oes-pattern-trigger-cell');
        trigger_cell.append(trigger);
        trigger_cell.append(actions);
        trigger_row.insertCell().innerHTML = ((values.required ?? false) ? '*' : '');

        /* create panel */
        let border_cell = config_row.insertCell(),
            panel = config_row.insertCell();
        border_cell.setAttribute('class', 'oes-pattern-border-cell');
        panel.setAttribute('colspan', '2');
        panel.appendChild(field_wrapper);
    }


    /**
     * Create a field for configuration of a pattern part.
     */
    function createField(i, key, type, title, value, help, el) {

        if (!el) {
            if (type === 'select') {
                el = createSelectField(i, key, value);
            } else {
                el = createInputField(type, value);
            }
        }
        el.setAttribute('id', 'oes_pattern_' + key + '_' + i);
        el.setAttribute('name', key);

        const field = document.createElement('div');
        field.setAttribute('class', 'oes-pattern-field');
        field.innerHTML = '<div class="oes-pattern-field-label"><strong>' + title + '</strong></div>';
        field.appendChild(el);
        field.innerHTML += '<small>' + help + '</small>';
        return field;
    }


    /**
     * Create an input field for the pattern configuration.
     */
    function createInputField(type, value) {
        let el = document.createElement('input');
        el.setAttribute('type', type);
        if (value !== undefined) el.setAttribute('value', value);
        return el;
    }


    /**
     * Create a select field for the pattern configuration.
     */
    function createSelectField(value) {
        const select = document.getElementById(pattern_ID + '-field');
        return createSelectFromCopy(select.options, ((value !== undefined) ? value : 'none'));
    }


    /**
     * Create select object from existing options (copy existing select with new value).
     */
    function createSelectFromCopy(options, value) {
        let select = document.createElement('select');
        for (let i = 0; i < options.length; i++) {
            const item = options[i];
            if (item.value === value) select[i] = new Option(item.text, item.value, true, true);
            else select[i] = new Option(item.text, item.value);
        }
        return select;
    }


    /**
     * Get pattern from fields.
     */
    function getPatternArray(){

        let patternArray = [];
        const inputs = ['string_value', 'required', 'prefix', 'suffix', 'separator'],
            selects = ['field_key', 'fallback'],
            rows = $('.oes-pattern-config-row');

        if (rows && rows.length > 0) {
            for (let i = 0; i < rows.length; i++) {

                patternArray[i] = {};

                /* get input values */
                inputs.forEach(function(inputName){
                    const test = '.' + rows[i].classList[0] + ' input[name="' + inputName + '"]';
                    const elements = $('.' + rows[i].classList[0] + ' input[name="' + inputName + '"]');
                    if (elements && elements.length > 0) {
                        patternArray[i][inputName] = elements[0].value;
                    }
                });

                /* get select values */
                selects.forEach(function(selectName){
                    const elements = $('.' + rows[i].classList[0] + ' select[name="' + selectName + '"]');
                    if (elements && elements.length > 0) {
                        patternArray[i][selectName] = elements[0].value;
                    }
                });
            }
        }

        return patternArray;
    }

}(window.oesPattern || (window.oesPattern = {}), jQuery));