import React, { Fragment } from 'react';
import '../css/h5p-listing-view.scss';

wp.hooks.addFilter('h5p-listing-view-additional-tab', 'h5p-group', tabs => {
    return [...tabs, {
        label: 'My group H5P content',
        slug: 'group'
    }];
});

wp.hooks.addFilter('h5p-listing-view-additional-filters', 'h5p-group', ( children, currentTab ) => {
    const options = Object.keys(ubc_h5p_adhocgroup.user_groups).map((groupIndex, index) => {
        return {
            label: ubc_h5p_adhocgroup.user_groups[groupIndex].name,
            value: ubc_h5p_adhocgroup.user_groups[groupIndex].term_id
        };
    });

    if( currentTab !== 2 ) {
        return children;
    }

    return (
        <Fragment>
            { children }
            <select
                className='h5p-filter-group'
                onChange={() => {
                    window.h5pTaxonomy.listView.doFetch();
                }}
            >
                <option value="">Select Group...</option>
                {options.map( (option, index) => {
                    return <option key={index} value={option.value}>{option.label}</option>
                } )}
            </select>
        </Fragment>
    );
});

wp.hooks.addFilter('h5p-listing-view-additional-form-data', 'h5p-group', (formData, currentTab) => {
    if( currentTab !== 2 ){
        return formData;
    }

    formData.append( 'group', document.querySelector('.h5p-filter-group').value);
    return formData;
});