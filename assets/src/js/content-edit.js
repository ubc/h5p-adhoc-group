import React, { Fragment, useEffect, useState, useRef } from 'react';
import Select from 'react-select';

export default () => {
    const [groupSelected, setGroupSelected] = useState([]);
    const options = useRef([]);

    useEffect(() => {
        const selectedGroupFromDB = ubc_h5p_group.content_group ? ubc_h5p_group.content_group : [];
        const newGroupSelected = [];

        options.current = Object.keys(ubc_h5p_group.user_group).map((groupIndex, index) => {
            const newOption = {
                label: ubc_h5p_group.user_group[groupIndex].name,
                value: ubc_h5p_group.user_group[groupIndex].term_id
            };

            if( selectedGroupFromDB.includes( newOption.value ) ) {
                newGroupSelected.push( newOption );
            }

            return newOption;
        });

        setGroupSelected( newGroupSelected );
    }, []);

    return (
        <Fragment>
            <div role="button" className="h5p-toggle" tabIndex="0" aria-expanded="true" aria-label="Toggle panel"></div>
            <h2>Group</h2>
            <div className="h5p-panel custom-taxonomy">
                <Select
                    value={groupSelected}
                    isMulti
                    options={options.current}
                    placeholder={'Select Group...'}
                    onChange={optionSelected => {
                        setGroupSelected(optionSelected);
                    }}
                />
            </div>
            <input
                type="hidden"
                value={ JSON.stringify({
                    group: groupSelected.map((group, index) => {
                        return group.value;
                    })
                }) }
                name="ubc-h5p-content-taxonomy-group"
            />
        </Fragment>
    );
}