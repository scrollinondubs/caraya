import React, { Component } from 'react';
import PropTypes from 'prop-types';

import fetchWP from '../utils/fetchWP';

import Notice from '../components/notice';

export default class Admin extends Component {
  constructor(props) {
    super(props);

    this.state = {
      dataUrl: '',
      savedDataUrl: '',
      notice: false
    };

    this.fetchWP = new fetchWP({
      restURL: this.props.wpObject.api_url,
      restNonce: this.props.wpObject.api_nonce,
    });

    this.getSetting();
  }

  getSetting = () => {
    this.fetchWP.get( 'admin' )
    .then(
      (json) => this.setState({
        dataUrl: json.value,
        savedDataUrl: json.value
      }),
      (err) => console.log( 'error', err )
    );
  };

  updateSetting = () => {
    this.fetchWP.post( 'admin', { dataUrl: this.state.dataUrl } )
    .then(
     (json) => this.processOkResponse(json, 'saved'),
      (err) => this.setState({
        notice: {
          type: 'error',
          message: err.message, // The error message returned by the REST API
        }
      })
    );
  }

  deleteSetting = () => {
    this.fetchWP.delete( 'admin' )
    .then(
      (json) => this.processOkResponse(json, 'deleted'),
      (err) => console.log('error', err)
    );
  }

  processOkResponse = (json, action) => {
    if (json.success) {
      this.setState({
        dataUrl: json.value,
        savedDataUrl: json.value,
        notice: {
          type: 'success',
          message: `Setting ${action} successfully.`,
        }
      });
    } else {
     this.setState({
        notice: {
          type: 'error',
          message: `Setting was not ${action}.`,
        }
      });
    }
  }

  updateInput = (event) => {
    this.setState({
      dataUrl: event.target.value,
    });
  }

  handleSave = (event) => {
    event.preventDefault();
    if (this.state.dataUrl === this.state.savedDataUrl) {
    this.setState({
      notice: {
        type: 'warning',
        message: 'Setting unchanged.',
      }
    });
    } else {
      this.updateSetting();
    }
  }

  clearNotice = () => {
    this.setState({
      notice: false,
    });
  }

  handleDelete = (event) => {
    event.preventDefault();
    this.deleteSetting();
  }

  render() {
    let notice;
    if ( this.state.notice ) {
      notice = <Notice notice={this.state.notice} onDismissClick={this.clearNotice} />
    }

    return (
      <div className="wrap">
       {notice}
        <form>
          <h1>Caraya Leaderboard url</h1>
          
          <label>
          Leaders Url:
            <input
              type="text"
              value={this.state.dataUrl}
              onChange={this.updateInput}
            />
          </label>

          <button
            id="save"
            className="button button-primary"
            onClick={this.handleSave}
          >Save</button>

          <button
            id="delete"
            className="button button-primary"
            onClick={this.handleDelete}
          >Delete</button>
        </form>
      </div>
    );
  }
}

Admin.propTypes = {
  wpObject: PropTypes.object
};