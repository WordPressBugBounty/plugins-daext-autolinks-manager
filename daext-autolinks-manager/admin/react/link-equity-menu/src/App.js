import Table from './components/Table';
import TableUrlDetails from './components/TableUrlDetails';
import {filterSelectStyles} from '../../utils/utils';
import RefreshIcon from '../../../assets/img/icons/refresh-cw-01.svg';
import LoadingScreen from "../../shared-components/LoadingScreen";
import Select from 'react-select';

const useState = wp.element.useState;
const useEffect = wp.element.useEffect;
const useRef = wp.element.useRef;

const {__} = wp.i18n;

/**
 * Filterable columns for the Link Equity menu.
 *
 * Each entry carries:
 *  - value : the column key sent to the REST API as `filter_column`
 *  - label : human-readable name shown in the dropdown
 *  - type  : 'text' → LIKE filter  |  'numeric' → equality / comparison filter
 */
const FILTERABLE_COLUMNS = [
    { value: 'url',          label: __( 'URL',            'daext-autolinks-manager' ), type: 'text'    },
    { value: 'iil',          label: __( 'Internal Links', 'daext-autolinks-manager' ), type: 'numeric' },
    { value: 'link_equity',  label: __( 'Link Equity',    'daext-autolinks-manager' ), type: 'numeric' },
];

let linkEquityDataLastUpdate = window.DAEXTAM_PARAMETERS.link_equity_data_last_update;
let linkEquityDataUpdateFrequency = window.DAEXTAM_PARAMETERS.link_equity_data_update_frequency;
let currentTime = window.DAEXTAM_PARAMETERS.current_time;

/**
 * Determines whether an automatic data update is needed based on the last update
 * timestamp and the configured update frequency.
 *
 * @returns {boolean}
 */
function isAutomaticUpdateNeeded() {

    if ( linkEquityDataLastUpdate === '' ) {
        return true;
    }

    let date = new Date( currentTime );

    switch ( linkEquityDataUpdateFrequency ) {

        case 'hourly':
            date.setHours( date.getHours() - 1 );
            return new Date( linkEquityDataLastUpdate ) < date;

        case 'daily':
            date.setDate( date.getDate() - 1 );
            return new Date( linkEquityDataLastUpdate ) < date;

        case 'weekly':
            date.setDate( date.getDate() - 7 );
            return new Date( linkEquityDataLastUpdate ) < date;

        case 'monthly':
            date.setMonth( date.getMonth() - 1 );
            return new Date( linkEquityDataLastUpdate ) < date;

    }

    return false;

}

const App = () => {

    const [formData, setFormData] = useState(
        {
            urlDetailsView: false,
            urlDetailsViewId: 0,
            urlDetailsViewUrl: '',
            searchString: '',
            searchStringChanged: false,
            sortingColumn: 'link_equity',
            sortingOrder: 'desc',
            filterColumn: 'url',
        }
    );

    const [dataAreLoading, setDataAreLoading] = useState(true);

    const [dataRefreshStatistics, setDataRefreshStatistics] = useState(false);

    /**
     * When the refresh completes, setDataUpdateRequired(false) and setFormData() are
     * called inside .then(), which changes useEffect dependencies and would trigger a second
     * redundant fetch. Setting this ref to true before those calls causes the next useEffect
     * run to bail out early, preventing the double fetch.
     */
    const skipNextFetch = useRef(false);

    const [tableData, setTableData] = useState([]);
    const [statistics, setStatistics] = useState({
        allUrls: 0,
        averageIil: 0,
        averageLinkEquity: 0
    });

    useEffect(() => {

        if (formData.urlDetailsView) {
            return;
        }

        let automaticUpdate = isAutomaticUpdateNeeded();

        /**
         * If an automatic data update is required, and it's not already set the dataRefreshStatistics state to true, set it
         * to true and return. By changing the dataRefreshStatistics state to true, useEffect will be triggered again and
         * this time with the dataRefreshStatistics state set to true, and the data will be updated.
         */
        if(automaticUpdate && !dataRefreshStatistics){

            linkEquityDataLastUpdate = currentTime;
            setDataRefreshStatistics(true);
            return;

        }

        // Bail out to prevent the redundant second fetch that is triggered when
        // setDataUpdateRequired(false) is called inside the .then() callback below.
        if (skipNextFetch.current) {
            skipNextFetch.current = false;
            return;
        }

        setDataAreLoading(true);

        /**
         * Initialize the chart data with the data received from the REST API
         * endpoint provided by the plugin.
         */
        wp.apiFetch({
            path: '/daext-autolinks-manager/v1/link-equity',
            method: 'POST',
            data: {
                search_string: formData.searchString,
                filter_column: formData.filterColumn,
                sorting_column: formData.sortingColumn,
                sorting_order: formData.sortingOrder,
                refresh_statistics: dataRefreshStatistics
            }
        }).then(data => {

                // Set the table data with setTableData().
                setTableData(data.table);

                // Set the statistics.
                setStatistics({
                    allPosts: data.statistics.all_urls,
                    averageMil: data.statistics.average_iil,
                    averageAil: data.statistics.average_link_equity
                });

                if (dataRefreshStatistics) {

                    // Flag the next useEffect run to be skipped before batching the
                    // state resets, so the re-render they trigger doesn't cause a second fetch.
                    skipNextFetch.current = true;

                    // Set the dataRefreshStatistics state to false.
                    setDataRefreshStatistics(false);

                    // Set the form data to the initial state.
                    setFormData({
                        urlDetailsView: false,
                        urlDetailsViewId: 0,
                        urlDetailsViewUrl: '',
                        searchString: '',
                        searchStringChanged: false,
                        sortingColumn: 'link_equity_relative',
                        sortingOrder: 'desc',
                        filterColumn: 'url',
                    });

                }

                setDataAreLoading(false);

            },
        );

    }, [
        formData.searchStringChanged,
        formData.sortingColumn,
        formData.sortingOrder,
        formData.urlDetailsView,
        dataRefreshStatistics
    ]);

    useEffect(() => {

        if (!formData.urlDetailsView) {
            return;
        }

        /**
         * Initialize the chart data with the data received from the REST API
         * endpoint provided by the plugin.
         */
        wp.apiFetch({
            path: '/daext-autolinks-manager/v1/link-equity-url',
            method: 'POST',
            data: {
                id: formData.urlDetailsViewId,
            }
        }).then(data => {

                // Set the table data with setTableData().
                setTableData(data);

            },
        );

    }, [
        formData.urlDetailsView
    ]);

    /**
     * Function to handle key press events.
     *
     * @param event
     */
    function handleKeyUp(event) {

        // Check if Enter key is pressed (key code 13).
        if (event.key === 'Enter') {
            event.preventDefault(); // Prevent form submission.
            document.getElementById('daextam-search-button').click(); // Simulate click on search button.
        }

    }

    /**
     * Returns the placeholder text for the search input based on the selected filter column.
     *
     * @param {string} filterColumn The currently selected filter column value.
     * @returns {string}
     */
    function getFilterPlaceholder( filterColumn ) {
        const col = FILTERABLE_COLUMNS.find( c => c.value === filterColumn );
        if ( ! col ) {
            return __( 'Filter…', 'daext-autolinks-manager' );
        }
        if ( col.type === 'numeric' ) {
            return __( 'e.g. 0, >5, <=10', 'daext-autolinks-manager' );
        }
        return __( 'Filter by', 'daext-autolinks-manager' ) + ' ' + col.label;
    }

    // Used by the Navigation component.
    function handleSortingChanges(e) {


        /**
         * Check if the sorting column is the same as the previous one.
         * If it is, change the sorting order.
         * If it is not, change the sorting column and set the sorting order to 'asc'.
         */
        let sortingOrder = formData.sortingOrder;
        if (formData.sortingColumn === e.target.value) {
            sortingOrder = formData.sortingOrder === 'asc' ? 'desc' : 'asc';
        }

        setFormData({
            ...formData,
            sortingColumn: e.target.value,
            sortingOrder: sortingOrder
        })

    }

    function urlDetailsViewHandler(id, url) {

        setFormData({
            ...formData,
            urlDetailsView: true,
            urlDetailsViewId: id,
            urlDetailsViewUrl: url
        });

    }

    // Used to toggle the dataRefreshStatistics value.
    function handleDataUpdateRequired(e) {
        setDataRefreshStatistics(prevDataRefreshStatistics => {
            return !prevDataRefreshStatistics;
        });
    }

    return (

        <>

            <React.StrictMode>

                {
                    !dataAreLoading ?

                        <>

                            {!formData.urlDetailsView && (
                                <div className={'daextam-react-table'}>

                                        <div className={'daextam-react-table-header'}>
                                            <div className={'statistics'}>
                                                <div title={__('The total number of URLs analyzed in the report.', 'daext-autolinks-manager')} className={'statistic-label'}>{__('All URLs', 'daext-autolinks-manager')}:</div>
                                                <div className={'statistic-value'}>{statistics.allPosts}</div>
                                                <div title={__('The average number of internal inbound links per post.', 'daext-autolinks-manager')} className={'statistic-label'}>{__('Avg. Inbound Links', 'daext-autolinks-manager')}:</div>
                                                <div className={'statistic-value'}>{statistics.averageMil}</div>
                                                <div title={__('The average link equity per post.', 'daext-autolinks-manager')} className={'statistic-label'}>{__('Avg. Equity', 'daext-autolinks-manager')}:</div>
                                                <div className={'statistic-value'}>{statistics.averageAil}</div>
                                            </div>
                                            <div className={'tools-actions'}>
                                                <button
                                                    onClick={(event) => handleDataUpdateRequired(event)}
                                                ><img src={RefreshIcon} className={'button-icon'}></img>
                                                    {__('Update metrics', 'daext-autolinks-manager')}
                                                </button>
                                            </div>
                                        </div>

                                        <div className={'daextam-react-table__daextam-filters daextam-react-table__daextam-filters-link-equity-menu'}>

                                            <div className={'daextam-search-container'}>
                                                <Select
                                                    classNamePrefix={'daextam-filter-select'}
                                                    isSearchable={false}
                                                    aria-label={__( 'Filter column', 'daext-autolinks-manager' )}
                                                    value={FILTERABLE_COLUMNS.find( col => col.value === formData.filterColumn ) || null}
                                                    onChange={(selectedOption) => setFormData({
                                                        ...formData,
                                                        filterColumn: selectedOption.value,
                                                        searchString: '',
                                                    })}
                                                    options={FILTERABLE_COLUMNS}
                                                    styles={filterSelectStyles}
                                                />
                                                <input onKeyUp={handleKeyUp} type={'text'}
                                                       placeholder={getFilterPlaceholder( formData.filterColumn )}
                                                       value={formData.searchString}
                                                       onChange={(event) => setFormData({
                                                           ...formData,
                                                           searchString: event.target.value
                                                       })}
                                                />
                                                <input id={'daextam-search-button'}
                                                       className={'daextam-btn daextam-btn-secondary'} type={'submit'}
                                                       value={__('Search', 'daext-autolinks-manager')}
                                                       onClick={() => setFormData({
                                                           ...formData,
                                                           searchStringChanged: formData.searchStringChanged ? false : true
                                                       })}
                                                />
                                            </div>

                                        </div>

                                        <Table
                                            data={tableData}
                                            handleSortingChanges={handleSortingChanges}
                                            formData={formData}
                                            urlDetailsViewHandler={urlDetailsViewHandler}
                                        />

                                    </div>
                            )}

                            {formData.urlDetailsView && (
                                <div className={'daextam-react-table url-details-view'}>


                                                <div className={'daextam-react-table-header'}>
                                                    <div>{__('Internal Inbound Links for', 'daext-autolinks-manager') + ' ' + formData.urlDetailsViewUrl}</div>
                                                    <a
                                                        className={'daextam-back-button'}
                                                        onClick={() => setFormData({
                                                            ...formData,
                                                            urlDetailsView: false,
                                                            urlDetailsViewId: 0
                                                        })}
                                                    >{String.fromCharCode(8592)} {__('Back', 'daext-autolinks-manager')}</a>
                                                </div>

                                                <TableUrlDetails
                                                    data={tableData}
                                                    handleSortingChanges={handleSortingChanges}
                                                    formData={formData}
                                                    urlDetailsViewHandler={urlDetailsViewHandler}
                                                />

                                            </div>
                            )}

                        </>

                        :

                        <LoadingScreen
                            loadingDataMessage={__('Loading data...', 'daext-autolinks-manager')}
                            generatingDataMessage={__('Data is being generated. For large sites, this process may take several minutes. Please wait...', 'daext-autolinks-manager')}
                            dataUpdateRequired={dataRefreshStatistics}/>
                }

            </React.StrictMode>

        </>

    );

};
export default App;