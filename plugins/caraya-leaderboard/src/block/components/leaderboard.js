import * as api from '../utils/api';

const { Component } = wp.element;

/**
 * Leaderboard Component
 */
export class Leaderboard extends Component {

  constructor(props) {
    super(...arguments);
    this.props = props;

    this.state = {
      leaders: [],
      loading: false,
      type: 'post',
      pages: {},
      pagesTotal: {},
      paging: false,
      initialLoading: false,
    };
    this.doPagination = this.doPagination.bind(this);
  }

  /**
   * When the component mounts it calls this function.
   * Fetches posts types, selected posts then makes first call for posts
   */
  componentDidMount() {
    this.setState({
      loading: true,
      initialLoading: true,
    });

    api.getLeaders(this.props.dataUrl)
      .then(({ data = {} } = {}) => {
      
        this.setState({
          leaders: data,
          loading: false
        }, () => {
         //fnished
        });
      });
  }

}

