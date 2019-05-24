import React, { Component } from 'react';
import PropTypes from 'prop-types';

import { Leaderboard } from '../components/leaderboard';

export default class Shortcode extends Component {
  render() {
    return (
     <div>
        <Leaderboard wpObject={this.props.wpObject} />
      </div>
    );
  }
}

Shortcode.propTypes = {
  wpObject: PropTypes.object
};