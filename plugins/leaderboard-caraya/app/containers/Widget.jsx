import React, { Component } from 'react';
import PropTypes from 'prop-types';

import { Leaderboard } from '../components/leaderboard';

export default class Widget extends Component {
  render() {
    return (
      <div>
        <section className="widget">
          <Leaderboard wpObject={this.props.wpObject} />
        </section>
      </div>
    );
  }
}

Widget.propTypes = {
  wpObject: PropTypes.object
};