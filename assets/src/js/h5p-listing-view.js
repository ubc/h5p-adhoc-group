import React, { Fragment } from 'react';
import '../css/h5p-listing-view.scss';

wp.hooks.addFilter('h5p-listing-view-additional-tab', 'h5p-group', tabs => {
    if( ubc_h5p_adhocgroup.is_user_admin ) {
        return tabs;
    }

    return [...tabs, {
        label: 'My Group H5P content',
        slug: 'group'
    }];
});

wp.hooks.addFilter('h5p-listing-view-additional-filters', 'h5p-group', ( children, currentTab ) => {
    const groups = ubc_h5p_adhocgroup.is_user_admin ? ubc_h5p_adhocgroup.all_groups : ubc_h5p_adhocgroup.user_groups;
    const options = Object.keys( groups ).map((groupIndex, index) => {
        return {
            label: groups[groupIndex].name,
            value: groups[groupIndex].term_id
        };
    });

    /**
     * Add Group filter to the tab section
     * 
     * Condition 1: User is not admin and tab is not group
     * Condition 2: User is admin and tab is not Community H5P Contents
     */
    if( ( ubc_h5p_adhocgroup.is_user_admin && currentTab.slug !== 'admin' ) || ( ! ubc_h5p_adhocgroup.is_user_admin && currentTab.slug !== 'group' ) ) {
        return children;
    }

    return (
        <Fragment>
            { children }
            <select
                className='h5p-filter-group'
                onChange={() => {
                    window.h5pTaxonomy.listView.fetchContent();
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
    if( ( ubc_h5p_adhocgroup.is_user_admin && currentTab.slug !== 'admin' ) || ( ! ubc_h5p_adhocgroup.is_user_admin && currentTab.slug !== 'group' ) ) {
        return formData;
    }

    formData.append( 'group', document.querySelector('.h5p-filter-group') ? document.querySelector('.h5p-filter-group').value : '');
    return formData;
});