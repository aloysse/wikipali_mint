import {Link} from 'umi';

type IProps = {

}

export default ({}: IProps) => {
  return (
    <div>
      <h1>List items page</h1>
      <Link to="/demo">Go home</Link>
      &nbsp;
      <Link to="/demo/course">课程主页</Link>
    </div>
  );
}
