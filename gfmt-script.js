const GFMT_PLUGIN_ID = 'global-field-management-tool';
const GFMT_SETTING_MENU_SLUG = GFMT_PLUGIN_ID+'-setting';

document.addEventListener('DOMContentLoaded', (event) => {
    const url = new URL(window.location.href);
    const pageSlug = url.searchParams.get('page'); // '/wp-admin/admin.php?page=XXX'
    if (pageSlug === GFMT_SETTING_MENU_SLUG) {
        const tableElem = document.getElementById('field-tabel'); // 動的に生成された要素のクリックイベントを拾うため、table自体を指定する
        add_click_event(tableElem);
        validate_form();
        add_row();
        if (get_tbody_rows_count(tableElem) > 1) replace_row();
    }
});

const add_click_event = (elem) => {
    elem.addEventListener('click', (event) => {
        if (event.target.id.indexOf('lock-field') !== -1) {
            // Lock Switch
            const index = event.target.dataset.index;
            const input = document.getElementById(`field-name-${index}`);
            if (input) {
                input.readOnly = event.target.checked;
            }
            const button = document.getElementById(`delete-row-${index}`);
            if (button) {
                button.disabled = event.target.checked;
            }
        } else if (event.target.id.indexOf('delete-row') !== -1) {
            // Delete Field
            if (get_tbody_rows_count(elem) < 2) {
                return false;
            }
            const tableRef = document.getElementById('field-tabel');
            const tr = event.target.parentNode.parentNode;
            tr.parentNode.deleteRow(tr.sectionRowIndex);
        }

    });
    /*
    const lockSulgChks = document.querySelectorAll(`[id^='lock-field]`);
    for ( const check of lockSulgChks ) {
        check.addEventListener('click', (event) => {
            const index = event.target.dataset.index;
            const input = document.getElementById(`field-slug-${index}`);
            if (input) {
                input.readOnly = event.target.checked;
            }
        });
    }
    */
}

const validate_form = () => {
    const sulgFields = document.querySelectorAll(`[id^='field-slug']`);
    const form = document.getElementById('gfmt-setting');
    form.addEventListener('submit', (event) => {
        event.preventDefault();
        for ( const field of sulgFields ) {
            if (!field.value) {
                alert(`Please enter 'Slug Field' on line ${parseInt(field.dataset.index)+1}.`);
                return false;
            }
        }
        form.submit();
    });
}

const add_row = () => {
    const elem = document.getElementById('add-filed');
    elem.addEventListener('click', (event) => {
        const tableRef = document.getElementById('field-tabel');
        const newIndex = get_new_row_index(tableRef);
        const newRow = tableRef.insertRow(-1);
        newRow.dataset.index = newIndex;

        const cellElem = [
            {
                tag: 'span',
                class: 'dashicons dashicons-menu',
                innerHTML: '',
            },
            {
                tag: 'label',
                class: 'toggle-button',
                childNode: {
                    tag: 'input',
                    id: 'lock-field-' + newIndex,
                    type: 'checkbox',
                    dataset: {
                        datasetName: 'data-index',
                        datasetValue: newIndex
                    },
                },
            },
            {
                tag: 'input',
                id: 'field-name-' + newIndex,
                type: 'text',
                maxlength: '32',
                name: 'field-name[]',
                required: 'required',
                dataset: {
                    datasetName: 'data-index',
                    datasetValue: newIndex
                },
            },
            {
                tag: 'input',
                id: 'field-value-' + newIndex,
                type: 'text',
                maxlength: '128',
                name: 'field-value[]',
                required: 'required',
            },
            {
                tag: 'button',
                id: 'delete-row-' + newIndex,
                type: 'button',
                class: 'button button-secondary',
                innerHTML: 'Delete Field',
                dataset: {
                    datasetName: 'data-index',
                    datasetValue: newIndex
                },
            },
        ];

        let parentElem = null;
        for (let i = 0; i < cellElem.length; i++) {
            let newCell = newRow.insertCell(i);
            if (i === 0) {
                newCell.setAttribute('class', 'draggable');
                newCell.setAttribute('draggable', 'true');
            }
            newCell.appendChild(create_element(cellElem[i]));
        }

        replace_row();

    });
}

const get_tbody_rows_count = (table) => {
    return table.tBodies[0].rows.length;
}

const get_new_row_index = (table) => {
    const tbodyChildren = table.tBodies[0].children;
    maxIndex = -1;
    for (let i = 0; i < tbodyChildren.length; i++) {
        const trIndex = parseInt(tbodyChildren[i].dataset.index);
        if ( trIndex !== undefined ) {
            if ( trIndex > maxIndex ) maxIndex = trIndex;
        }
    }
    return maxIndex + 1;
}

const create_element = (elementData) => {

    let elemInput = document.createElement(elementData.tag);

    for (const [key, value] of Object.entries(elementData)) {
        if (key !== 'tag') {
            switch (key) {
                case 'dataset':
                    elemInput.setAttribute(value.datasetName, value.datasetValue);
                    break;
                case 'childNode':
                    elemInput.appendChild(create_element(value)); // 子ノードのエレメントの場合は、それを生成するために再帰的に呼び出す
                    break;
                case 'innerHTML':
                    elemInput.innerHTML = value;
                    break;
                default:
                    elemInput.setAttribute(key, value);
                    break;
            }
        }
    }

    return elemInput;
}

const replace_row = () => {
    let draggedRow = null;

    const tableBody = document.querySelector('#field-tabel tbody');
    tableBody.addEventListener('dragstart', (e) => {
        if (e.target.classList.contains('draggable')) {
          draggedRow = e.target.parentElement;
          draggedRow.classList.add('transparent');
          e.dataTransfer.effectAllowed = 'move';
        }
    });

    tableBody.addEventListener('dragend', () => {
        if (draggedRow) {
          draggedRow.classList.remove('transparent');
          removeDropIndicators();
        }
    });

    tableBody.addEventListener('dragover', (e) => {
        e.preventDefault();
        const targetRow = e.target.closest('tr');
        if (targetRow && draggedRow !== targetRow) {
        const draggingDownwards = e.clientY > targetRow.getBoundingClientRect().top + (targetRow.offsetHeight / 2);
        removeDropIndicators();
        targetRow.classList.add(draggingDownwards ? 'drop-below' : 'drop-above');
        }
    });

    tableBody.addEventListener('dragleave', (e) => {
        removeDropIndicators();
    });

    tableBody.addEventListener('drop', (e) => {
        const targetRow = e.target.closest('tr');
        if (targetRow && draggedRow !== targetRow) {
          e.preventDefault();
          removeDropIndicators();
          const draggingDownwards = e.clientY > targetRow.getBoundingClientRect().top + targetRow.offsetHeight / 2;
          if (draggingDownwards) {
            targetRow.parentNode.insertBefore(draggedRow, targetRow.nextSibling);
          } else {
            targetRow.parentNode.insertBefore(draggedRow, targetRow);
          }
        }
      });
    
    const removeDropIndicators = () => {
        document.querySelectorAll('.drop-above, .drop-below').forEach(row => {
          row.classList.remove('drop-above', 'drop-below');
        });
    }
}